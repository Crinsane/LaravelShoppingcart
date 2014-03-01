<?php namespace Gloudemans\Shoppingcart;

use Illuminate\Support\Collection;

class Cart {

	/**
	 * Session class instance
	 *
	 * @var Session
	 */
	protected $session;

	/**
	 * Event class instance
	 *
	 * @var Event
	 */
	protected $event;

	/**
	 * Current cart instance
	 *
	 * @var string
	 */
	protected $instance;

	/**
	 * Constructor
	 *
	 * @param Session $session Session class instance
	 * @param Event   $event   Event class instance
	 */
	public function __construct($session, $event)
	{
		$this->session = $session;
		$this->event = $event;

		$this->instance = 'main';
	}

	/**
	 * Set the current cart instance
	 *
	 * @param  string $instance Cart instance name
	 * @return Cart
	 */
	public function instance($instance = null)
	{
		if(empty($instance)) throw new Exceptions\ShoppingcartInstanceException;

		$this->instance = $instance;

		// Return self so the method is chainable
		return $this;
	}

	/**
	 * Add a row to the cart
	 *
	 * @param string|Array $id      Unique ID of the item|Item formated as array|Array of items
	 * @param string 	   $name    Name of the item
	 * @param int    	   $qty     Item qty to add to the cart
	 * @param float  	   $price   Price of one item
	 * @param Array  	   $options Array of additional options, such as 'size' or 'color'
	 */
	public function add($id, $name = null, $qty = null, $price = null, Array $options = array())
	{
		// If the first parameter is an array we need to call the add() function again
		if(is_array($id))
		{
			// And if it's not only an array, but a multidimensional array, we need to
			// recursively call the add function
			if($this->is_multi($id))
			{
				// Fire the cart.batch event
				$this->event->fire('cart.batch', $id);

				foreach($id as $item)
				{
					$options = isset($item['options']) ? $item['options'] : array();
					$this->addRow($item['id'], $item['name'], $item['qty'], $item['price'], $options);
				}

				return;
			}

			$options = isset($id['options']) ? $id['options'] : array();

			// Fire the cart.add event
			$this->event->fire('cart.add', array_merge($id, array('options' => $options)));

			return $this->addRow($id['id'], $id['name'], $id['qty'], $id['price'], $options);
		}

		// Fire the cart.add event
		$this->event->fire('cart.add', compact('id', 'name', 'qty', 'price', 'options'));

		return $this->addRow($id, $name, $qty, $price, $options);
	}

	/**
	 * Update the quantity of one row of the cart
	 *
	 * @param  string        $rowId       The rowid of the item you want to update
	 * @param  integer|Array $attribute   New quantity of the item|Array of attributes to update
	 * @return boolean
	 */
	public function update($rowId, $attribute)
	{
		if( ! $this->hasRowId($rowId)) throw new Exceptions\ShoppingcartInvalidRowIDException;

		if(is_array($attribute))
		{
			// Fire the cart.update event
			$this->event->fire('cart.update', $rowId);

			return $this->updateAttribute($rowId, $attribute);
		}

		// Fire the cart.update event
		$this->event->fire('cart.update', $rowId);

		return $this->updateQty($rowId, $attribute);
	}

	/**
	 * Remove a row from the cart
	 *
	 * @param  string  $rowId The rowid of the item
	 * @return boolean
	 */
	public function remove($rowId)
	{
		if( ! $this->hasRowId($rowId)) throw new Exceptions\ShoppingcartInvalidRowIDException;

		$cart = $this->getContent();

		// Fire the cart.remove event
		$this->event->fire('cart.remove', $rowId);

		$cart->forget($rowId);

		return $this->updateCart($cart);
	}

	/**
	 * Get a row of the cart by its ID
	 *
	 * @param  string $rowId The ID of the row to fetch
	 * @return CartCollection
	 */
	public function get($rowId)
	{
		$cart = $this->getContent();

		return ($cart->has($rowId)) ? $cart->get($rowId) : NULL;
	}

	/**
	 * Get the cart content
	 *
	 * @return CartRowCollection
	 */
	public function content()
	{
		$cart = $this->getContent();

		return (empty($cart)) ? NULL : $cart;
	}

	/**
	 * Empty the cart
	 *
	 * @return boolean
	 */
	public function destroy()
	{
		// Fire the cart.destroy event
		$this->event->fire('cart.destroy');

		return $this->updateCart(NULL);
	}

	/**
	 * Get the price total
	 *
	 * @return float
	 */
	public function total()
	{
		$total = 0;
		$cart = $this->getContent();

		if(empty($cart))
		{
			return $total;
		}

		foreach($cart AS $row)
		{
			$total += $row->subtotal;
		}

		return $total;
	}

	/**
	 * Get the number of items in the cart
	 *
	 * @param  boolean $totalItems Get all the items (when false, will return the number of rows)
	 * @return int
	 */
	public function count($totalItems = true)
	{
		$cart = $this->getContent();

		if( ! $totalItems)
		{
			return $cart->count();
		}

		$count = 0;

		foreach($cart AS $row)
		{
			$count += $row->qty;
		}

		return $count;
	}

	/**
	 * Search if the cart has a item
	 *
	 * @param  Array  $search An array with the item ID and optional options
	 * @return Array|boolean
	 */
	public function search(Array $search)
	{
		foreach($this->getContent() as $item)
		{
			$found = $item->search($search);

			if($found)
			{
				$rows[] = $item->rowid;
			}
		}

		return (empty($rows)) ? false : $rows;
	}

	/**
	 * Add row to the cart
	 *
	 * @param string $id      Unique ID of the item
	 * @param string $name    Name of the item
	 * @param int    $qty     Item qty to add to the cart
	 * @param float  $price   Price of one item
	 * @param Array  $options Array of additional options, such as 'size' or 'color'
	 */
	protected function addRow($id, $name, $qty, $price, Array $options = array())
	{
		if(empty($id) || empty($name) || empty($qty) || empty($price))
		{
			throw new Exceptions\ShoppingcartInvalidItemException;
		}

		if( ! is_numeric($qty))
		{
			throw new Exceptions\ShoppingcartInvalidQtyException;
		}

		if( ! is_numeric($price))
		{
			throw new Exceptions\ShoppingcartInvalidPriceException;
		}

		$cart = $this->getContent();

		$rowId = $this->generateRowId($id, $options);

		if($cart->has($rowId))
		{
			$row = $cart->get($rowId);
			$cart = $this->updateRow($rowId, array('qty' => $row->qty + $qty));
		}
		else
		{
			$cart = $this->createRow($rowId, $id, $name, $qty, $price, $options);
		}

		return $this->updateCart($cart);
	}

	/**
	 * Generate a unique id for the new row
	 *
	 * @param  string  $id      Unique ID of the item
	 * @param  Array   $options Array of additional options, such as 'size' or 'color'
	 * @return boolean
	 */
	protected function generateRowId($id, $options)
	{
		ksort($options);

		return md5($id . serialize($options));
	}

	/**
	 * Check if a rowid exists in the current cart instance
	 *
	 * @param  string  $id  Unique ID of the item
	 * @return boolean
	 */
	protected function hasRowId($rowId)
	{
		return $this->getContent()->has($rowId);
	}

	/**
	 * Update the cart
	 *
	 * @param  CartCollection  $cart The new cart content
	 * @return void
	 */
	protected function updateCart($cart)
	{
		return $this->session->put($this->getInstance(), $cart);
	}

	/**
	 * Get the carts content, if there is no cart content set yet, return a new empty Collection
	 *
	 * @return Illuminate\Support\Collection
	 */
	protected function getContent()
	{
		$content = ($this->session->has($this->getInstance())) ? $this->session->get($this->getInstance()) : new CartCollection;

		return $content;
	}

	/**
	 * Get the current cart instance
	 *
	 * @return string
	 */
	protected function getInstance()
	{
		return 'cart.' . $this->instance;
	}

	/**
	 * Update a row if the rowId already exists
	 *
	 * @param  string  $rowId The ID of the row to update
	 * @param  integer $qty   The quantity to add to the row
	 * @return Collection
	 */
	protected function updateRow($rowId, $attributes)
	{
		$cart = $this->getContent();

		$row = $cart->get($rowId);

		foreach($attributes as $key => $value)
		{
			if($key == 'options')
			{
				$options = $row->options->merge($value);
				$row->put($key, $options);
			}
			else
			{
				$row->put($key, $value);
			}
		}

		if( ! is_null(array_keys($attributes, array('qty', 'price'))))
		{
			$row->put('subtotal', $row->qty * $row->price);
		}

		$cart->put($rowId, $row);

		return $cart;
	}

	/**
	 * Create a new row Object
	 *
	 * @param  string $rowId   The ID of the new row
	 * @param  string $id      Unique ID of the item
	 * @param  string $name    Name of the item
	 * @param  int    $qty     Item qty to add to the cart
	 * @param  float  $price   Price of one item
	 * @param  Array  $options Array of additional options, such as 'size' or 'color'
	 * @return Collection
	 */
	protected function createRow($rowId, $id, $name, $qty, $price, $options)
	{
		$cart = $this->getContent();

		$newRow = new CartRowCollection(array(
			'rowid' => $rowId,
			'id' => $id,
			'name' => $name,
			'qty' => $qty,
			'price' => $price,
			'options' => new CartRowOptionsCollection($options),
			'subtotal' => $qty * $price
		));

		$cart->put($rowId, $newRow);

		return $cart;
	}

	/**
	 * Update the quantity of a row
	 *
	 * @param  string $rowId The ID of the row
	 * @param  int    $qty   The qty to add
	 * @return CartCollection
	 */
	protected function updateQty($rowId, $qty)
	{
		if($qty <= 0)
		{
			return $this->remove($rowId);
		}

		return $this->updateRow($rowId, array('qty' => $qty));
	}

	/**
	 * Update an attribute of the row
	 *
	 * @param  string $rowId      The ID of the row
	 * @param  Array  $attributes An array of attributes to update
	 * @return CartCollection
	 */
	protected function updateAttribute($rowId, $attributes)
	{
		return $this->updateRow($rowId, $attributes);
	}

	/**
	 * Check if the array is a multidimensional array
	 *
	 * @param  Array   $array The array to check
	 * @return boolean
	 */
	protected function is_multi(Array $array)
	{
	    $first = array_shift($array);

	    return is_array($first);
	}

}
