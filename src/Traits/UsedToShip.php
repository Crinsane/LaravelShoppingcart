<?php

namespace Gloudemans\Shoppingcart\Traits;

trait UsedToShip
{
    /**
     * Get the identifier of the Shippable item.
     *
     * @return int|string
     */
    public function getShippableIdentifier()
    {
        return method_exists($this, 'getKey') ? $this->getKey() : $this->id;
    }

    /**
     * Get the description or title of the Shippable item.
     *
     * @return string
     */
    public function getShippableDescription()
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
    public function getShippablePrice()
    {
        if (property_exists($this, 'price')) {
            return $this->price;
        }
        return null;
    }
}