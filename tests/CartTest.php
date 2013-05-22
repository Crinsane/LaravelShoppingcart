<?php

class CartTest extends TestCase {

    public function testCartCanAdd()
    {
        Cart::add(1, 'test', 1, 10.00, ['size' => 'L']);

        $this->assertEquals(Cart::count(), 1);
        $this->assertInstanceOf('Gloudemans\Shoppingcart\CartCollection', Cart::content());
    }

    public function testCartCanAddToExisting()
    {
        Cart::add(1, 'test', 1, 10.00, ['size' => 'L']);
        Cart::add(1, 'test', 1, 10.00, ['size' => 'L']);

        $rowId = Cart::content()->first()->rowid;

        $this->assertEquals(Cart::get($rowId)->qty, 2);
        $this->assertInstanceOf('Gloudemans\Shoppingcart\CartCollection', Cart::content());
    }

    public function testCartCanUpdate()
    {
        Cart::add(1, 'test', 1, 10.00, ['size' => 'L']);

        $rowId = Cart::content()->first()->rowid;

        Cart::update($rowId, 2);

        $this->assertEquals(Cart::get($rowId)->qty, 2);
        $this->assertInstanceOf('Gloudemans\Shoppingcart\CartCollection', Cart::content());
    }

    public function testCartCanRemove()
    {
        Cart::add(1, 'test', 1, 10.00, ['size' => 'L']);
        Cart::add(2, 'test', 1, 10.00, ['size' => 'L']);

        $rowId = Cart::content()->first()->rowid;

        Cart::remove($rowId);

        $this->assertEquals(Cart::count(), 1);
        $this->assertNull(Cart::get($rowId));
        $this->assertInstanceOf('Gloudemans\Shoppingcart\CartCollection', Cart::content());
    }

    public function testCartCanRemoveOnUpdate()
    {
        Cart::add(1, 'test', 1, 10.00, ['size' => 'L']);

        $rowId = Cart::content()->first()->rowid;

        Cart::update($rowId, 0);

        $this->assertEquals(Cart::count(), 0);
        $this->assertNull(Cart::get($rowId));
        $this->assertInstanceOf('Gloudemans\Shoppingcart\CartCollection', Cart::content());
    }

    public function testCartCanGet()
    {
        Cart::add(1, 'test', 1, 10.00, ['size' => 'L']);

        $rowId = Cart::content()->first()->rowid;

        $row = Cart::get($rowId);

        $this->assertEquals($row->id, 1);
        $this->assertEquals($row->name, 'test');
        $this->assertEquals($row->qty, 1);
        $this->assertEquals($row->price, 10.00);
        $this->assertInstanceOf('Gloudemans\Shoppingcart\CartRowCollection', $row);
        $this->assertInstanceOf('Gloudemans\Shoppingcart\CartRowOptionsCollection', $row->options);
        $this->assertEquals($row, Cart::content()->first());
        $this->assertInstanceOf('Gloudemans\Shoppingcart\CartCollection', Cart::content());
    }

    public function testCartCanGetContent()
    {
        Cart::add(1, 'test', 1, 10.00, ['size' => 'L']);

        $this->assertEquals(Cart::content()->count(), 1);
        $this->assertInstanceOf('Gloudemans\Shoppingcart\CartCollection', Cart::content());
    }

    public function testCartCanDestroy()
    {
        Cart::add(1, 'test', 1, 10.00, ['size' => 'L']);

        Cart::destroy();

        $this->assertEquals(Cart::count(), 0);
        $this->assertInstanceOf('Gloudemans\Shoppingcart\CartCollection', Cart::content());
    }

    public function testCartCanGetTotal()
    {
        Cart::add(1, 'test', 1, 10.00, ['size' => 'L']);
        Cart::add(2, 'test', 1, 10.00, ['size' => 'L']);

        $total = Cart::total();

        $this->assertTrue(is_float($total));
        $this->assertEquals($total, 20.00);
    }

    public function testCartCanGetCount()
    {
        Cart::add(1, 'test', 1, 10.00, ['size' => 'L']);
        Cart::add(2, 'test', 2, 10.00, ['size' => 'L']);

        $count = Cart::count(false);

        $this->assertTrue(is_integer($count));
        $this->assertEquals($count, 2);

        $count = Cart::count();

        $this->assertTrue(is_integer($count));
        $this->assertEquals($count, 3);
    }

}