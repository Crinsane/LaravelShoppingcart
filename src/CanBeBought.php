<?php

namespace Gloudemans\Shoppingcart;

trait CanBeBought
{

    /**
     * Checks if a given property exists
     *
     * @param string $propertyName
     *
     * @return bool
     */
    private function hasProperty($propertyName)
    {
        // Checks if property exists on the attributes array
        if (isset($this->attributes) && array_key_exists($propertyName, $this->attributes)) {
            return true;
        }

        // Checks if property is set on the model
        if (property_exists($this, $propertyName)) {
            return true;
        }

        // Checks if there is a mutator for this property
        if (method_exists($this, 'hasGetMutator')) {
            return $this->hasGetMutator($propertyName);
        }

        return false;
    }

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
        if ($this->hasProperty('name')) {
            return $this->name;
        }

        if ($this->hasProperty('title')) {
            return $this->title;
        }

        if ($this->hasProperty('description')) {
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
        if ($this->hasProperty('price')) {
            return $this->price;
        }

        if ($this->hasProperty('value')) {
            return $this->value;
        }

        if ($this->hasProperty('amount')) {
            return $this->amount;
        }

        return null;
    }
}
