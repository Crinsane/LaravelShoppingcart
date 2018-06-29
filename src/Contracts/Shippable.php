<?php

namespace Gloudemans\Shoppingcart\Contracts;

interface Shippable
{
    /**
     * Get the identifier of the Buyable item.
     *
     * @return int|string
     */
    public function getShippableIdentifier();

    /**
     * Get the description or title of the Buyable item.
     *
     * @return string
     */
    public function getShippableDescription();

    /**
     * Get the price of the Buyable item.
     *
     * @return float
     */
    public function getShippablePrice();
}