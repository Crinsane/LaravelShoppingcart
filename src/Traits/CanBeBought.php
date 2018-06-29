<?php

namespace Gloudemans\Shoppingcart\Traits;

trait CanBeBought
{
    /**
     * Get the identifier of the Buyable item.
     *
     * @return int|string
     */
    public function getBuyableIdentifier($options = null)
    {
        return method_exists($this, 'getKey') ? $this->getKey() : $this->id;
    }

    /**
     * Get the description or title of the Buyable item.
     *
     * @return string
     */
    public function getBuyableDescription($options = null)
    {
        if (property_exists($this, 'name')) {
            return $this->name;
        }
        if (property_exists($this, 'title')) {
            return $this->title;
        }
        if (property_exists($this, 'description')) {
            return $this->description;
        }
        return null;
    }

    /**
     * Get the price of the Buyable item.
     *
     * @return float
     */
    public function getBuyablePrice($options = null)
    {
        if (property_exists($this, 'price')) {
            return $this->price;
        }
        return null;
    }
}