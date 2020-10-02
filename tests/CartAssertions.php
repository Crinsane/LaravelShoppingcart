<?php

namespace Gloudemans\Tests\Shoppingcart;

use Gloudemans\Shoppingcart\Cart;
use PHPUnit\Framework\Assert as PHPUnit;

trait CartAssertions
{
    /**
     * Assert that the cart contains the given number of items.
     *
     * @param int|float                     $items
     * @param \Gloudemans\Shoppingcart\Cart $cart
     */
    public function assertItemsInCart($items, Cart $cart)
    {
        $actual = $cart->count();

        PHPUnit::assertSame($items, $cart->count(), "Expected the cart to contain {$items} items, but got {$actual}.");
    }

    /**
     * Assert that the cart contains the given number of rows.
     *
     * @param int                           $rows
     * @param \Gloudemans\Shoppingcart\Cart $cart
     */
    public function assertRowsInCart($rows, Cart $cart)
    {
        $actual = $cart->content()->count();

        PHPUnit::assertSame($rows, $cart->content(), "Expected the cart to contain {$rows} rows, but got {$actual}.");
    }
}
