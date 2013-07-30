<?php

class CartTest extends TestCase {

	public function testCartCanAdd()
	{
		Cart::add(1, 'test', 1, 10.00, array('size' => 'L'));

		$this->assertEquals(Cart::count(), 1);
		$this->assertInstanceOf('Gloudemans\Shoppingcart\CartCollection', Cart::content());
	}

	public function testCartCanAddArray()
	{
		Cart::add(array('id' => 1, 'name' => 'test', 'qty' => 1, 'price' => 10.00, 'options' => array('size' => 'L')));

		$this->assertEquals(Cart::count(), 1);
		$this->assertInstanceOf('Gloudemans\Shoppingcart\CartCollection', Cart::content());
	}

	public function testCartCanAddBatch()
	{
		Cart::add(array(
			array('id' => 1, 'name' => 'test_1', 'qty' => 1, 'price' => 10.00),
			array('id' => 2, 'name' => 'test_2', 'qty' => 1, 'price' => 10.00, 'options' => array('size' => 'large'))
		));

		$this->assertEquals(Cart::count(), 2);
		$this->assertInstanceOf('Gloudemans\Shoppingcart\CartCollection', Cart::content());
	}

	public function testCartCanAddToExisting()
	{
		Cart::add(1, 'test', 1, 10.00, array('size' => 'L'));
		Cart::add(1, 'test', 1, 10.00, array('size' => 'L'));

		$rowId = Cart::content()->first()->rowid;

		$this->assertEquals(Cart::get($rowId)->qty, 2);
		$this->assertInstanceOf('Gloudemans\Shoppingcart\CartCollection', Cart::content());
	}

	public function testCartCanUpdate()
	{
		Cart::add(1, 'test', 1, 10.00, array('size' => 'L'));

		$rowId = Cart::content()->first()->rowid;

		Cart::update($rowId, 2);

		$this->assertEquals(Cart::get($rowId)->qty, 2);
		$this->assertEquals(Cart::get($rowId)->subtotal, 20.00);
		$this->assertInstanceOf('Gloudemans\Shoppingcart\CartCollection', Cart::content());
	}

	public function testCartCanUpdateAttribute()
	{
		Cart::add(1, 'test', 1, 10.00, array('size' => 'L'));

		$rowId = Cart::content()->first()->rowid;

		Cart::update($rowId, array('name' => 'test_2'));

		$this->assertEquals(Cart::get($rowId)->name, 'test_2');
		$this->assertInstanceOf('Gloudemans\Shoppingcart\CartCollection', Cart::content());
	}

	public function testCartCanUpdateOptionsAttribute()
	{
		Cart::add(1, 'test', 1, 10.00, array('size' => 'L'));

		$rowId = Cart::content()->first()->rowid;

		Cart::update($rowId, array('options' => array('color' => 'yellow')));

		$this->assertEquals(Cart::get($rowId)->options, new Gloudemans\Shoppingcart\CartRowOptionsCollection(array('size' => 'L', 'color' => 'yellow')));
		$this->assertInstanceOf('Gloudemans\Shoppingcart\CartRowOptionsCollection', Cart::get($rowId)->options);
	}

	public function testCartCanRemove()
	{
		Cart::add(1, 'test', 1, 10.00, array('size' => 'L'));
		Cart::add(2, 'test', 1, 10.00, array('size' => 'L'));

		$rowId = Cart::content()->first()->rowid;

		Cart::remove($rowId);

		$this->assertEquals(Cart::count(), 1);
		$this->assertNull(Cart::get($rowId));
		$this->assertInstanceOf('Gloudemans\Shoppingcart\CartCollection', Cart::content());
	}

	public function testCartCanRemoveOnUpdate()
	{
		Cart::add(1, 'test', 1, 10.00, array('size' => 'L'));

		$rowId = Cart::content()->first()->rowid;

		Cart::update($rowId, 0);

		$this->assertEquals(Cart::count(), 0);
		$this->assertNull(Cart::get($rowId));
		$this->assertInstanceOf('Gloudemans\Shoppingcart\CartCollection', Cart::content());
	}

	public function testCartCanGet()
	{
		Cart::add(1, 'test', 1, 10.00, array('size' => 'L'));

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
		Cart::add(1, 'test', 1, 10.00, array('size' => 'L'));

		$this->assertEquals(Cart::content()->count(), 1);
		$this->assertInstanceOf('Gloudemans\Shoppingcart\CartCollection', Cart::content());
	}

	public function testCartCanDestroy()
	{
		Cart::add(1, 'test', 1, 10.00, array('size' => 'L'));

		Cart::destroy();

		$this->assertEquals(Cart::count(), 0);
		$this->assertInstanceOf('Gloudemans\Shoppingcart\CartCollection', Cart::content());
	}

	public function testCartCanGetTotal()
	{
		Cart::add(1, 'test', 1, 10.00, array('size' => 'L'));
		Cart::add(2, 'test', 1, 10.00, array('size' => 'L'));

		$total = Cart::total();

		$this->assertTrue(is_float($total));
		$this->assertEquals($total, 20.00);
	}

	public function testCartCanGetCount()
	{
		Cart::add(1, 'test', 1, 10.00, array('size' => 'L'));
		Cart::add(2, 'test', 2, 10.00, array('size' => 'L'));

		$count = Cart::count(false);

		$this->assertTrue(is_integer($count));
		$this->assertEquals($count, 2);

		$count = Cart::count();

		$this->assertTrue(is_integer($count));
		$this->assertEquals($count, 3);
	}

	public function testCartCanHaveMultipleInstances()
	{
		Cart::instance('test_1')->add(1, 'test_1', 1, 10.00, array('size' => 'L'));
		Cart::instance('test_2')->add(2, 'test_2', 2, 10.00, array('size' => 'L'));

		$name = Cart::instance('test_1')->content()->first()->name;

		$this->assertEquals($name, 'test_1');

		$name = Cart::instance('test_2')->content()->first()->name;

		$this->assertEquals($name, 'test_2');

		$count = Cart::count();

		$this->assertEquals($count, 2);

		Cart::add(3, 'test_3', 3, 10.00);

		$count = Cart::count();

		$this->assertEquals($count, 5);

		Cart::instance('test_1')->add(1, 'test_1', 1, 10.00, array('size' => 'L'));

		$count = Cart::count();

		$this->assertEquals($count, 2);
	}

	public function testCartCanSearch()
	{
		Cart::add(1, 'Product 1', 1, 10.00, array('size' => 'large', 'color' => 'red'));
		Cart::add(2, 'Product 2', 1, 10.00, array('size' => 'large'));

		$row1 = '17351068ba2f30bac8d3ccb1ad94dfe6';
		$row2 = '5b9865a287421917623bdcf99f7de904';

		$search = Cart::search(array('id' => 1, 'name' => 'Product 1'));
		$this->assertEquals($search, array($row1));

		$search = Cart::search(array('id' => 2, 'name' => 'Product 1'));
		$this->assertFalse($search);

		$search = Cart::search(array('id' => 2, 'name' => 'Product 2'));
		$this->assertEquals($search, array($row2));

		$search = Cart::search(array('id' => 1, 'price' => 10.00));
		$this->assertEquals($search, array($row1));

		$search = Cart::search(array('qty' => 1, 'price' => 10.00));
		$this->assertEquals($search, array($row1, $row2));

		$search = Cart::search(array('id' => 2, 'kaas' => 'Product 2'));
		$this->assertFalse($search);

		$search = Cart::search(array('id' => 2, 'options' => array('size' => 'large')));
		$this->assertEquals($search, array($row2));

		$search = Cart::search(array('options' => array('size' => 'large')));
		$this->assertEquals($search, array($row1, $row2));

		$search = Cart::search(array('id' => 1, 'options' => array('color' => 'red')));
		$this->assertEquals($search, array($row1));

		$search = Cart::search(array('options' => array('color' => 'red', 'size' => 'large')));
		$this->assertEquals($search, array($row1));
	}

	/**
     * @expectedException Gloudemans\Shoppingcart\Exceptions\ShoppingcartInstanceException
     */
	public function testShoppingcartInstanceException()
	{
		Cart::instance();
	}

	/**
     * @expectedException Gloudemans\Shoppingcart\Exceptions\ShoppingcartInvalidItemException
     */
	public function testShoppingcartInvalidItemException()
	{
		Cart::add(1);
	}

	/**
     * @expectedException Gloudemans\Shoppingcart\Exceptions\ShoppingcartInvalidQtyException
     */
	public function testShoppingcartInvalidQtyException()
	{
		Cart::add(1, 'Product 1', 'nonnumeric', 10.00);
	}

	/**
     * @expectedException Gloudemans\Shoppingcart\Exceptions\ShoppingcartInvalidPriceException
     */
	public function testShoppingcartInvalidPriceException()
	{
		Cart::add(1, 'Product 1', 1, 'nonnumeric');
	}

	/**
     * @expectedException Gloudemans\Shoppingcart\Exceptions\ShoppingcartInvalidRowIDException
     */
	public function testShoppingcartInvalidRowIDException()
	{
		Cart::add(1, 'Product 1', 1, 10.00);

		Cart::update('nonexistingrowid', 2);
	}

}