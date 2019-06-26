<?php

namespace Gloudemans\Tests\Shoppingcart\Fixtures;

use Gloudemans\Shoppingcart\Contracts\InstanceIdentifier;

class Identifiable implements InstanceIdentifier
{
    /**
     * @var int|string
     */
    private $identifier;

    /**
     * @var int
     */
    private $discountRate;

    /**
     * BuyableProduct constructor.
     *
     * @param int|string $id
     * @param string     $name
     * @param float      $price
     */
    public function __construct($identifier = 'identifier', $discountRate = 0)
    {
        $this->identifier = $identifier;
        $this->discountRate = $discountRate;
    }

    /**
     * Get the unique identifier to load the Cart from.
     *
     * @return int|string
     */
    public function getInstanceIdentifier($options = null)
    {
        return $this->identifier;
    }

    /**
     * Get the unique identifier to load the Cart from.
     *
     * @return int|string
     */
    public function getInstanceGlobalDiscount($options = null)
    {
        return $this->discountRate;
    }
}
