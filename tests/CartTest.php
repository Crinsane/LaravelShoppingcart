<?php

use Gloudemans\Shoppingcart\Cart;
use Gloudemans\Shoppingcart\CartCollection;
use Gloudemans\Shoppingcart\CartRowCollection;
use Gloudemans\Shoppingcart\CartRowOptionsCollection;
use Mockery as m;

require_once 'SessionMock.php';

class CartTest extends PHPUnit_Framework_TestCase {

	protected $events;
	protected $cart;

	public function setUp()
	{
		$session= new SessionMock;
		$this->events = m::mock('Illuminate\Events\Dispatcher');

		$this->cart = new Cart($session, $this->events);
	}

	public function tearDown()
	{
		m::close();
	}

	public function testCartCanAdd()
	{
		$this->events->shouldReceive('fire')->once()->with('cart.add', m::type('array'));

		$this->cart->add('293ad', 'Product 1', 1.0, 1, 9.99, array('size' => 'large'));
	}

	public function testCartCanAddArray()
	{
		$this->events->shouldReceive('fire')->once()->with('cart.add', m::type('array'));

		$this->cart->add(array('id' => '293ad', 'name' => 'Product 1', 'weight' => 1.0, 'qty' => 1, 'price' => 9.99, 'options' => array('size' => 'large')));
	}

	public function testCartCanAddBatch()
	{
		$this->events->shouldReceive('fire')->once()->with('cart.batch', m::type('array'));

		$this->cart->add(array(
			array('id' => '293ad', 'name' => 'Product 1', 'weight' => 1.0, 'qty' => 1, 'price' => 10.00),
			array('id' => '4832k', 'name' => 'Product 2', 'weight' => 2.0, 'qty' => 1, 'price' => 10.00, 'options' => array('size' => 'large'))
		));
	}

	public function testCartCanAddMultipleOptions()
	{
		$this->events->shouldReceive('fire')->once()->with('cart.add', m::type('array'));

		$this->cart->add('293ad', 'Product 1', 1, 9.99, array('size' => 'large', 'color' => 'red'));

		$cartRow = $this->cart->get('c5417b5761c7fb837e4227a38870dd4d');

		$this->assertInstanceOf('Gloudemans\Shoppingcart\CartRowOptionsCollection', $cartRow->options);
		$this->assertEquals('large', $cartRow->options->size);
		$this->assertEquals('red', $cartRow->options->color);
	}

	/**
	 * @expectedException Gloudemans\Shoppingcart\Exceptions\ShoppingcartInvalidItemException
	 */
	public function testCartThrowsExceptionOnEmptyItem()
	{
		$this->events->shouldReceive('fire')->once()->with('cart.add', m::any());

		$this->cart->add('', '', '', '');
	}

	/**
	 * @expectedException Gloudemans\Shoppingcart\Exceptions\ShoppingcartInvalidQtyException
	 */
	public function testCartThrowsExceptionOnNoneNumericQty()
	{
		$this->events->shouldReceive('fire')->once()->with('cart.add', m::any());

		$this->cart->add('293ad', 'Product 1', 1.0, 'none-numeric', 9.99);
	}

	/**
	 * @expectedException Gloudemans\Shoppingcart\Exceptions\ShoppingcartInvalidPriceException
	 */
	public function testCartThrowsExceptionOnNoneNumericPrice()
	{
		$this->events->shouldReceive('fire')->once()->with('cart.add', m::any());

		$this->cart->add('293ad', 'Product 1', 1.0, 1, 'none-numeric');
	}

	/**
	 * @expectedException Gloudemans\Shoppingcart\Exceptions\ShoppingcartInvalidWeightException
	 */
	public function testCartThrowsExceptionOnNoneNumericWeight()
	{
		$this->events->shouldReceive('fire')->once()->with('cart.add', m::any());

		$this->cart->add('293ad', 'Product 1', 'none-numeric', 1, 9.99);
	}

	public function testCartCanUpdateExistingItem()
	{
		$this->events->shouldReceive('fire')->twice()->with('cart.add', m::type('array'));

		$this->cart->add('293ad', 'Product 1', 1.0, 1, 9.99);
		$this->cart->add('293ad', 'Product 1', 1.0, 1, 9.99);

		$this->assertEquals(2, $this->cart->content()->first()->qty);
	}

	public function testCartCanUpdateQty()
	{
		$this->events->shouldReceive('fire')->once()->with('cart.add', m::type('array'));
		$this->events->shouldReceive('fire')->once()->with('cart.update', m::type('string'));

		$this->cart->add('293ad', 'Product 1', 1.0, 1, 9.99);
		$this->cart->update('8cbf215baa3b757e910e5305ab981172', 2);

		$this->assertEquals(2, $this->cart->content()->first()->qty);
	}

	public function testCartCanUpdateItem()
	{
		$this->events->shouldReceive('fire')->once()->with('cart.add', m::type('array'));
		$this->events->shouldReceive('fire')->once()->with('cart.update', m::type('string'));

		$this->cart->add('293ad', 'Product 1', 1.0, 1, 9.99);
		$this->cart->update('8cbf215baa3b757e910e5305ab981172', array('name' => 'Product 2'));

		$this->assertEquals('Product 2', $this->cart->content()->first()->name);
	}

	public function testCartCanUpdateOptions()
	{
		$this->events->shouldReceive('fire')->once()->with('cart.add', m::type('array'));
		$this->events->shouldReceive('fire')->once()->with('cart.update', m::type('string'));

		$this->cart->add('293ad', 'Product 1', 1.0, 1, 9.99, array('size' => 'S'));
		$this->cart->update('9be7e69d236ca2d09d2e0838d2c59aeb', array('options' => array('size' => 'L')));

		$this->assertEquals('L', $this->cart->content()->first()->options->size);
	}

	/**
	 * @expectedException Gloudemans\Shoppingcart\Exceptions\ShoppingcartInvalidRowIDException
	 */
	public function testCartThrowsExceptionOnInvalidRowId()
	{
		$this->cart->update('invalidRowId', 1);
	}

	public function testCartCanRemove()
	{
		$this->events->shouldReceive('fire')->once()->with('cart.add', m::type('array'));
		$this->events->shouldReceive('fire')->once()->with('cart.remove', m::type('string'));

		$this->cart->add('293ad', 'Product 1', 1.0, 1, 9.99);
		$this->cart->remove('8cbf215baa3b757e910e5305ab981172');

		$this->assertTrue($this->cart->content()->isEmpty());
	}

	public function testCartCanRemoveOnUpdate()
	{
		$this->events->shouldReceive('fire')->once()->with('cart.add', m::type('array'));
		$this->events->shouldReceive('fire')->once()->with('cart.update', m::type('string'));
		$this->events->shouldReceive('fire')->once()->with('cart.remove', m::type('string'));

		$this->cart->add('293ad', 'Product 1', 1.0, 1, 9.99);
		$this->cart->update('8cbf215baa3b757e910e5305ab981172', 0);

		$this->assertTrue($this->cart->content()->isEmpty());
	}

	public function testCartCanRemoveOnNegativeUpdate()
	{
		$this->events->shouldReceive('fire')->once()->with('cart.add', m::type('array'));
		$this->events->shouldReceive('fire')->once()->with('cart.update', m::type('string'));
		$this->events->shouldReceive('fire')->once()->with('cart.remove', m::type('string'));

		$this->cart->add('293ad', 'Product 1', 1.0, 1, 9.99);
		$this->cart->update('8cbf215baa3b757e910e5305ab981172', -1);

		$this->assertTrue($this->cart->content()->isEmpty());
	}

	public function testCartCanGet()
	{
		$this->events->shouldReceive('fire')->once()->with('cart.add', m::type('array'));

		$this->cart->add('293ad', 'Product 1', 1.0, 1, 9.99);
		$item = $this->cart->get('8cbf215baa3b757e910e5305ab981172');

		$this->assertEquals('293ad', $item->id);
	}

	public function testCartCanGetContent()
	{
		$this->events->shouldReceive('fire')->once()->with('cart.add', m::type('array'));

		$this->cart->add('293ad', 'Product 1', 1.0, 1, 9.99);

		$this->assertInstanceOf('Gloudemans\Shoppingcart\CartCollection', $this->cart->content());
		$this->assertFalse($this->cart->content()->isEmpty());
	}

	public function testCartCanDestroy()
	{
		$this->events->shouldReceive('fire')->once()->with('cart.add', m::type('array'));
		$this->events->shouldReceive('fire')->once()->with('cart.destroy');

		$this->cart->add('293ad', 'Product 1', 1.0, 1, 9.99);
		$this->cart->destroy();

		$this->assertInstanceOf('Gloudemans\Shoppingcart\CartCollection', $this->cart->content());
		$this->assertTrue($this->cart->content()->isEmpty());
	}

	public function testCartCanGetTotal()
	{
		$this->events->shouldReceive('fire')->twice()->with('cart.add', m::type('array'));

		$this->cart->add('293ad', 'Product 1', 1.0, 1, 9.99);
		$this->cart->add('986se', 'Product 2', 1.0, 1, 19.99);

		$this->assertEquals(29.98, $this->cart->total());
	}

	public function testCartCanGetItemCount()
	{
		$this->events->shouldReceive('fire')->twice()->with('cart.add', m::type('array'));

		$this->cart->add('293ad', 'Product 1', 1.0, 1, 9.99);
		$this->cart->add('986se', 'Product 2', 2.0, 2, 19.99);

		$this->assertEquals(3, $this->cart->count());
	}

	public function testCartCanGetRowCount()
	{
		$this->events->shouldReceive('fire')->twice()->with('cart.add', m::type('array'));

		$this->cart->add('293ad', 'Product 1', 1.0, 1, 9.99);
		$this->cart->add('986se', 'Product 2', 2.0, 2, 19.99);

		$this->assertEquals(2, $this->cart->count(false));
	}

	public function testCartCanSearch()
	{
		$this->events->shouldReceive('fire')->once()->with('cart.add', m::type('array'));

		$this->cart->add('293ad', 'Product 1', 1.0, 1, 9.99);

		$searchResult = $this->cart->search(array('id' => '293ad'));
		$this->assertEquals('8cbf215baa3b757e910e5305ab981172', $searchResult[0]);
	}

	public function testCartCanHaveMultipleInstances()
	{
		$this->events->shouldReceive('fire')->twice()->with('cart.add', m::type('array'));

		$this->cart->instance('firstInstance')->add('293ad', 'Product 1', 1.0, 1, 9.99);
		$this->cart->instance('secondInstance')->add('986se', 'Product 2', 2.0, 1, 19.99);

		$this->assertTrue($this->cart->instance('firstInstance')->content()->has('8cbf215baa3b757e910e5305ab981172'));
		$this->assertFalse($this->cart->instance('firstInstance')->content()->has('22eae2b9c10083d6631aaa023106871a'));
		$this->assertTrue($this->cart->instance('secondInstance')->content()->has('22eae2b9c10083d6631aaa023106871a'));
		$this->assertFalse($this->cart->instance('secondInstance')->content()->has('8cbf215baa3b757e910e5305ab981172'));
	}

	/**
	 * @expectedException Gloudemans\Shoppingcart\Exceptions\ShoppingcartInstanceException
	 */
	public function testCartThrowsExceptionOnEmptyInstance()
	{
		$this->cart->instance();
	}

	public function testCartReturnsCartCollection()
	{
		$this->events->shouldReceive('fire')->once()->with('cart.add', m::type('array'));

		$this->cart->add('293ad', 'Product 1', 1.0, 1, 9.99);

		$this->assertInstanceOf('Gloudemans\Shoppingcart\CartCollection', $this->cart->content());
	}

	public function testCartCollectionHasCartRowCollection()
	{
		$this->events->shouldReceive('fire')->once()->with('cart.add', m::type('array'));

		$this->cart->add('293ad', 'Product 1', 1.0, 1, 9.99);

		$this->assertInstanceOf('Gloudemans\Shoppingcart\CartRowCollection', $this->cart->content()->first());
	}

	public function testCartRowCollectionHasCartRowOptionsCollection()
	{
		$this->events->shouldReceive('fire')->once()->with('cart.add', m::type('array'));

		$this->cart->add('293ad', 'Product 1', 1.0, 1, 9.99);

		$this->assertInstanceOf('Gloudemans\Shoppingcart\CartRowOptionsCollection', $this->cart->content()->first()->options);
	}

}