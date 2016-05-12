<?php

namespace Gloudemans\Shoppingcart\Contracts;

interface Buyable
{
    /**
     * Get the identifier of the Buyable item.
     *
     * @return int|string
     */
    public function getBuyableIdentifier();

    /**
     * Get the description or title of the Buyable item.
     *
     * @return string
     */
    public function getBuyableDescription();

    /**
     * Get the price of the Buyable item.
     *
     * @return float
     */
    public function getBuyablePrice();
}