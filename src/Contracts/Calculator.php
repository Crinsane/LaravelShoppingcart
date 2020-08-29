<?php

namespace Gloudemans\Shoppingcart\Contracts;

use Gloudemans\Shoppingcart\CartItem;

interface Calculator
{
    static function getAttribute(string $attribute, CartItem $cartItem);
}