<?php

namespace Gloudemans\Shoppingcart\Calculation;

use Gloudemans\Shoppingcart\CartItem;
use Gloudemans\Shoppingcart\Contracts\Calculator;

class GrossPrice implements Calculator
{
    public static function getAttribute(string $attribute, CartItem $cartItem)
    {
        $decimals = config('cart.format.decimals', 2);

        switch ($attribute) {
            case 'priceNet':
                return round($cartItem->price / (1 + ($cartItem->taxRate / 100)), $decimals);
            case 'discount':
                return $cartItem->priceNet * ($cartItem->getDiscountRate() / 100);
            case 'tax':
                return round($cartItem->priceTarget * ($cartItem->taxRate / 100), $decimals);
            case 'priceTax':
                return round($cartItem->priceTarget + $cartItem->tax, $decimals);
            case 'discountTotal':
                return round($cartItem->discount * $cartItem->qty, $decimals);
            case 'priceTotal':
                return round($cartItem->priceNet * $cartItem->qty, $decimals);
            case 'subtotal':
                return max(round($cartItem->priceTotal - $cartItem->discountTotal, $decimals), 0);
            case 'priceTarget':
                return round(($cartItem->priceTotal - $cartItem->discountTotal) / $cartItem->qty, $decimals);
            case 'taxTotal':
                return round($cartItem->subtotal * ($cartItem->taxRate / 100), $decimals);
            case 'total':
                return round($cartItem->subtotal + $cartItem->taxTotal, $decimals);
            default:
                return;
        }
    }
}
