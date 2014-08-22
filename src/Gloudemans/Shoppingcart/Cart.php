<?php namespace Gloudemans\Shoppingcart;

use Illuminate\Support\Collection;

class Cart {

	/**
	 * Session class instance
	 *
	 * @var Illuminate\Session\SessionManager
	 */
	protected $session;

	/**
	 * Event class instance
	 *
	 * @var Illuminate\Events\Dispatcher
	 */
	protected $event;

	/**
	 * Current cart instance
	 *
	 * @var string
	 */
	protected $instance;

	/**
	 * The Eloquent model a cart is associated with
	 *
	 * @var string
	 */
	protected $associatedModel;

	/**
	 * An optional namespace for the associated model
	 *
	 * @var string
	 */
	protected $associatedModelNamespace;

	/**
	 * Constructor
	 *
	 * @param Illuminate\Session\SessionManager  $session  Session class instance
	 * @param Illuminate\Events\Dispatcher       $event    Event class instance
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
	 * @param  string  $instance  Cart instance name
	 * @return Gloudemans\Shoppingcart\Cart
	 */
	public function instance($instance = null)
	{
		if(empty($instance)) throw new Exceptions\ShoppingcartInstanceException;

		$this->instance = $instance;

		// Return self so the method is chainable
		return $this;
	}

	/**
	 * Set the associated model
	 *
	 * @param  string    $modelName        The name of the model
	 * @param  string    $modelNamespace   The namespace of the model
	 * @return void
	 */
	public function associate($modelName, $modelNamespace = null)
	{
		$this->associatedModel = $modelName;
		$this->associatedModelNamespace = $modelNamespace;

		if( ! class_exists($modelNamespace . '\\' . $modelName)) throw new Exceptions\ShoppingcartUnknownModelException;

		// Return self so the method is chainable
		return $this;
	}

	/**
	 * Add a row to the cart
	 *
	 * @param string|array  $id       Unique ID of the item|Item formated as array|Array of items
	 * @param string 	    $name     Name of the item
	 * @param int    	    $qty      Item qty to add to the cart
	 * @param float  	    $price    Price of one item
	 * @param array  	    $options  Array of additional options, such as 'size' or 'color'
	 */
	public function add($id, $name = null, $qty = null, $price = null, array $options = array())
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
					$options = array_get($item, 'options', array());
					$this->addRow($item['id'], $item['name'], $item['qty'], $item['price'], $options);
				}

				// Fire the cart.batched event
				$this->event->fire('cart.batched', $id);

				return;
			}

			$options = array_get($id, 'options', array());

			// Fire the cart.add event
			$this->event->fire('cart.add', array_merge($id, array('options' => $options)));

			$result = $this->addRow($id['id'], $id['name'], $id['qty'], $id['price'], $options);

			// Fire the cart.added event
			$this->event->fire('cart.added', array_merge($id, array('options' => $options)));

			return $result;
		}

		// Fire the cart.add event
		$this->event->fire('cart.add', compact('id', 'name', 'qty', 'price', 'options'));

		$result = $this->addRow($id, $name, $qty, $price, $options);

		// Fire the cart.added event
		$this->event->fire('cart.added', compact('id', 'name', 'qty', 'price', 'options'));

		return $result;
	}

	/**
	 * Update the quantity of one row of the cart
	 *
	 * @param  string         $rowId       The rowid of the item you want to update
	 * @param  integer|array  $attribute   New quantity of the item|Array of attributes to update
	 * @return boolean
	 */
	public function update($rowId, $attribute)
	{
		if( ! $this->hasRowId($rowId)) throw new Exceptions\ShoppingcartInvalidRowIDException;

		if(is_array($attribute))
		{
			// Fire the cart.update event
			$this->event->fire('cart.update', $rowId);

			$result = $this->updateAttribute($rowId, $attribute);

			// Fire the cart.updated event
			$this->event->fire('cart.updated', $rowId);

			return $result;
		}

		// Fire the cart.update event
		$this->event->fire('cart.update', $rowId);

		$result = $this->updateQty($rowId, $attribute);

		// Fire the cart.updated event
		$this->event->fire('cart.updated', $rowId);

		return $result;
	}

	/**
	 * Remove a row from the cart
	 *
	 * @param  string  $rowId  The rowid of the item
	 * @return boolean
	 */
	public function remove($rowId)
	{
		if( ! $this->hasRowId($rowId)) throw new Exceptions\ShoppingcartInvalidRowIDException;

		$cart = $this->getContent();

		// Fire the cart.remove event
		$this->event->fire('cart.remove', $rowId);

		$cart->forget($rowId);

		// Fire the cart.removed event
		$this->event->fire('cart.removed', $rowId);

		return $this->updateCart($cart);
	}

	/**
	 * Get a row of the cart by its ID
	 *
	 * @param  string  $rowId  The ID of the row to fetch
	 * @return Gloudemans\Shoppingcart\CartCollection
	 */
	public function get($rowId)
	{
		$cart = $this->getContent();

		return ($cart->has($rowId)) ? $cart->get($rowId) : NULL;
	}

	/**
	 * Get the cart content
	 *
	 * @return Gloudemans\Shoppingcart\CartRowCollection
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

		$result = $this->updateCart(NULL);

		// Fire the cart.destroyed event
		$this->event->fire('cart.destroyed');

		return $result;
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
	 * @param  boolean  $totalItems  Get all the items (when false, will return the number of rows)
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
	 * @param  array  $search  An array with the item ID and optional options
	 * @return array|boolean
	 */
	public function search(array $search)
	{
		if(empty($search)) return false;

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
	 * @param string  $id       Unique ID of the item
	 * @param string  $name     Name of the item
	 * @param int     $qty      Item qty to add to the cart
	 * @param float   $price    Price of one item
	 * @param array   $options  Array of additional options, such as 'size' or 'color'
	 */
	protected function addRow($id, $name, $qty, $price, array $options = array())
	{
		if(empty($id) || empty($name) || empty($qty) || ! isset($price))
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
	 * @param  string  $id       Unique ID of the item
	 * @param  array   $options  Array of additional options, such as 'size' or 'color'
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
	 * @param  Gloudemans\Shoppingcart\CartCollection  $cart  The new cart content
	 * @return void
	 */
	protected function updateCart($cart)
	{
		return $this->session->put($this->getInstance(), $cart);
	}

	/**
	 * Get the carts content, if there is no cart content set yet, return a new empty Collection
	 *
	 * @return Gloudemans\Shoppingcart\CartCollection
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
	 * @param  string   $rowId  The ID of the row to update
	 * @param  integer  $qty    The quantity to add to the row
	 * @return Gloudemans\Shoppingcart\CartCollection
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
	 * @param  string  $rowId    The ID of the new row
	 * @param  string  $id       Unique ID of the item
	 * @param  string  $name     Name of the item
	 * @param  int     $qty      Item qty to add to the cart
	 * @param  float   $price    Price of one item
	 * @param  array   $options  Array of additional options, such as 'size' or 'color'
	 * @return Gloudemans\Shoppingcart\CartCollection
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
		), $this->associatedModel, $this->associatedModelNamespace);

		$cart->put($rowId, $newRow);

		return $cart;
	}

	/**
	 * Update the quantity of a row
	 *
	 * @param  string  $rowId  The ID of the row
	 * @param  int     $qty    The qty to add
	 * @return Gloudemans\Shoppingcart\CartCollection
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
	 * @param  string  $rowId       The ID of the row
	 * @param  array   $attributes  An array of attributes to update
	 * @return Gloudemans\Shoppingcart\CartCollection
	 */
	protected function updateAttribute($rowId, $attributes)
	{
		return $this->updateRow($rowId, $attributes);
	}

	/**
	 * Check if the array is a multidimensional array
	 *
	 * @param  array   $array  The array to check
	 * @return boolean
	 */
	protected function is_multi(array $array)
	{
		return is_array(head($array));
	}

}
