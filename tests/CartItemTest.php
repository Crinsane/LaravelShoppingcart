<?php

namespace Gloudemans\Tests\Shoppingcart;

use Gloudemans\Shoppingcart\CartItem;
use Gloudemans\Shoppingcart\ShoppingcartServiceProvider;
use Orchestra\Testbench\TestCase;

class CartItemTest extends TestCase
{
    /**
     * Set the package service provider.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [ShoppingcartServiceProvider::class];
    }

    /** @test */
    public function it_can_be_cast_to_an_array()
    {
        $cartItem = new CartItem(1, 'Some item', 10.00, 550, ['size' => 'XL', 'color' => 'red']);
        $cartItem->setQuantity(2);

        $this->assertEquals([
            'id'      => 1,
            'name'    => 'Some item',
            'price'   => 10.00,
            'rowId'   => '07d5da5550494c62daf9993cf954303f',
            'qty'     => 2,
            'options' => [
                'size'  => 'XL',
                'color' => 'red',
            ],
            'tax'      => 0,
            'subtotal' => 20.00,
            'discount' => 0.0,
            'weight'   => 550.0,
        ], $cartItem->toArray());
    }

    /** @test */
    public function it_can_be_cast_to_json()
    {
        $cartItem = new CartItem(1, 'Some item', 10.00, 550, ['size' => 'XL', 'color' => 'red']);
        $cartItem->setQuantity(2);

        $this->assertJson($cartItem->toJson());

        $json = '{"rowId":"07d5da5550494c62daf9993cf954303f","id":1,"name":"Some item","qty":2,"price":10,"weight":550,"options":{"size":"XL","color":"red"},"discount":0,"tax":0,"subtotal":20}';

        $this->assertEquals($json, $cartItem->toJson());
    }
}
