<?php

namespace Gloudemans\Shoppingcart\Contracts;

use Gloudemans\Shoppingcart\CartItem;

interface Calculator
{
    public static function getAttribute(string $attribute, CartItem $cartItem);
}
