<?php

namespace Gloudemans\Shoppingcart\Contracts;

interface Discountable
{
    /**
     * Get the identifier of the Buyable item.
     *
     * @return int|string
     */
    public function getDiscountableIdentifier();

    /**
     * Get the description or title of the Buyable item.
     *
     * @return string
     */
    public function getDiscountableDescription();

    /**
     * Get the price of the Buyable item.
     *
     * @return float
     */
    public function getDiscountableValue();

    /**
     * Get the price of the Buyable item.
     *
     * @return float
     */
    public function getDiscountableType();
}