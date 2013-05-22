<?php namespace Gloudemans\Shoppingcart;

use Illuminate\Support\Collection;
use Money\Money;

class Cart {

    /**
     * Session class instance
     * 
     * @var Session
     */
    protected $session;

    /**
     * Constructor
     * 
     * @param Session $session Session class instance
     */
    public function __construct($session)
    {
        $this->session = $session;
    }

    /**
     * Add a row to the cart
     * 
     * @param string $id      Unique ID of the item
     * @param string $name    Name of the item
     * @param int    $qty     Item qty to add to the cart
     * @param float  $price   Price of one item
     * @param Array  $options Array of additional options, such as 'size' or 'color'
     */
    public function add($id, $name, $qty, $price, $options = array())
    {
        $cart = $this->getContent();

        $rowId = $this->generateRowId($id, $options);

        if($cart->has($rowId))
        {
            $row = $cart->get($rowId);
            $cart = $this->updateRow($rowId, $row->qty + $qty); 
        }
        else
        {
            $cart = $this->createRow($rowId, $id, $name, $qty, $price, $options);
        }

        return $this->updateCart($cart);
    }

    /**
     * Update the quantity of one row of the cart
     * @param  string  $rowId The rowid of the item you want to update
     * @param  integer $qty   New quantity of the item
     * @return boolean
     */
    public function update($rowId, $qty)
    {
        if($qty == 0)
        {
            return $this->remove($rowId);
        }

        return $this->updateRow($rowId, $qty);
    }

    /**
     * Remove a row from the cart
     * 
     * @param  string  $rowId The rowid of the item
     * @return boolean   
     */
    public function remove($rowId)
    {
        $cart = $this->getContent();

        $cart->forget($rowId);

        return $this->updateCart($cart);
    }

    /**
     * Get a row of the cart by its ID
     * 
     * @param  string $rowId The ID of the row to fetch
     * @return Array
     */
    public function get($rowId)
    {
        $cart = $this->getContent();

        return ($cart->has($rowId)) ? $cart->get($rowId) : NULL;
    }

    /**
     * Get the cart content
     * 
     * @return Array
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
     * @return int
     */
    public function count($totalItems = TRUE)
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
     * Generate a unique id for the new row
     * 
     * @param  string  $id      Unique ID of the item
     * @param  Array   $options Array of additional options, such as 'size' or 'color'
     * @return boolean
     */
    protected function generateRowId($id, $options)
    {
        return md5($id . serialize($options));
    }

    /**
     * Update the cart
     * @param  Array   $cart The new cart content
     * @return void
     */
    protected function updateCart($cart)
    {
        return $this->session->put('cart', $cart);
    }

    /**
     * Get the carts content, if there is no cart content set yet, return a new empty Collection
     * 
     * @return Illuminate\Support\Collection
     */
    protected function getContent()
    {
        $content = ($this->session->has('cart')) ? $this->session->get('cart') : new CartCollection;

        return $content;
    }

    /**
     * Update a row if the rowId already exists
     * 
     * @param  string  $rowId The ID of the row to update   
     * @param  integer $qty   The quantity to add to the row
     * @return Collection
     */
    protected function updateRow($rowId, $qty)
    {
        $cart = $this->getContent();

        $row = $cart->get($rowId);

        $row->qty = $qty;
        $row->subtotal = $row->qty * $row->price;

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

        $newRow = new CartRowCollection([
            'rowid' => $rowId,
            'id' => $id,
            'name' => $name,
            'qty' => $qty,
            'price' => $price,
            'options' => new CartRowOptionsCollection($options),
            'subtotal' => $qty * $price
        ]);

        $cart->put($rowId, $newRow);

        return $cart;
    }

}