<?php

namespace Gloudemans\Shoppingcart\Contracts;

use Closure;

/**
 * Interface CartContract
 * @package Gloudemans\Shoppingcart\Contracts
 */
interface CartContract
{
    /**
     * Set the current cart instance.
     *
     * @param string|null $instance
     * @return CartContract
     */
    public function instance($instance = null);

    /**
     * Get the current cart instance.
     *
     * @return string
     */
    public function currentInstance();

    /**
     * Add an item to the cart.
     *
     * @param mixed $id
     * @param mixed $name
     * @param int|float $qty
     * @param float $price
     * @param array $options
     * @return \Gloudemans\Shoppingcart\CartItem
     */
    public function add($id, $name = null, $qty = null, $price = null, array $options = []);

    /**
     * Update the cart item with the given rowId.
     *
     * @param string $rowId
     * @param mixed $qty
     * @return void
     */
    public function update($rowId, $qty);

    /**
     * Remove the cart item with the given rowId from the cart.
     *
     * @param string $rowId
     * @return void
     */
    public function remove($rowId);

    /**
     * Get a cart item from the cart by its rowId.
     *
     * @param string $rowId
     * @return \Gloudemans\Shoppingcart\CartItem
     */
    public function get($rowId);

    /**
     * Destroy the current cart instance.
     *
     * @return void
     */
    public function destroy();

    /**
     * Get the content of the cart.
     *
     * @return \Illuminate\Support\Collection
     */
    public function content();

    /**
     * Get the number of items in the cart.
     *
     * @return int|float
     */
    public function count();

    /**
     * Get the total price of the items in the cart.
     *
     * @param int $decimals
     * @param string $decimalPoint
     * @param string $thousandSeperator
     * @return string
     */
    public function total($decimals = 2, $decimalPoint = '.', $thousandSeperator = ',');

    /**
     * Get the total tax of the items in the cart.
     *
     * @param int $decimals
     * @param string $decimalPoint
     * @param string $thousandSeperator
     * @return float
     */
    public function tax($decimals = 2, $decimalPoint = '.', $thousandSeperator = ',');

    /**
     * Get the subtotal (total - tax) of the items in the cart.
     *
     * @param int $decimals
     * @param string $decimalPoint
     * @param string $thousandSeperator
     * @return float
     */
    public function subtotal($decimals = 2, $decimalPoint = '.', $thousandSeperator = ',');

    /**
     * Search the cart content for a cart item matching the given search closure.
     *
     * @param \Closure $search
     * @return \Illuminate\Support\Collection
     */
    public function search(Closure $search);

    /**
     * Associate the cart item with the given rowId with the given model.
     *
     * @param string $rowId
     * @param mixed $model
     * @return void
     */
    public function associate($rowId, $model);

    /**
     * Set the tax rate for the cart item with the given rowId.
     *
     * @param string $rowId
     * @param int|float $taxRate
     * @return void
     */
    public function setTax($rowId, $taxRate);

    /**
     * Store an the current instance of the cart.
     *
     * @param mixed $identifier
     * @return void
     */
    public function store($identifier);

    /**
     * Restore the cart with the given identifier.
     *
     * @param mixed $identifier
     * @return void
     */
    public function restore($identifier);
}