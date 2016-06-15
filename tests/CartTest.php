<?php

use Gloudemans\Shoppingcart\Cart;
use Gloudemans\Shoppingcart\Contracts\Buyable;
use Gloudemans\Shoppingcart\Contracts\Taxable;

class CartTest extends Orchestra\Testbench\TestCase
{
    use CartAssertions;

    /**
     * Set the package service provider.
     *
     * @param \Illuminate\Foundation\Application $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [\Gloudemans\Shoppingcart\ShoppingcartServiceProvider::class];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('cart.database.connection', 'testing');

        $app['config']->set('session.driver', 'array');

        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }
    
    /** @test */
    public function it_has_a_default_instance()
    {
        $cart = $this->getCart();

        $this->assertEquals(Cart::DEFAULT_INSTANCE, $cart->currentInstance());
    }

    /** @test */
    public function it_can_have_multiple_instances()
    {
        $cart = $this->getCart();

        $item = $this->getBuyableMock(1, 'First item');

        $cart->add($item);

        $item2 = $this->getBuyableMock(2, 'Second item');

        $cart->instance('wishlist')->add($item2);

        $this->assertItemsInCart(1, $cart->instance(Cart::DEFAULT_INSTANCE));
        $this->assertItemsInCart(1, $cart->instance('wishlist'));
    }
    
    /** @test */
    public function it_can_add_an_item()
    {
        $this->expectsEvents('cart.added');

        $cart = $this->getCart();

        $item = $this->getBuyableMock();

        $cart->add($item);

        $this->assertEquals(1, $cart->count());
    }

    /** @test */
    public function it_will_return_the_cartitem_of_the_added_item()
    {
        $this->expectsEvents('cart.added');

        $cart = $this->getCart();

        $item = $this->getBuyableMock();

        $cartItem = $cart->add($item);

        $this->assertInstanceOf(\Gloudemans\Shoppingcart\CartItem::class, $cartItem);
        $this->assertEquals('027c91341fd5cf4d2579b49c4b6a90da', $cartItem->rowId);
    }

    /** @test */
    public function it_can_add_multiple_buyable_items_at_once()
    {
        $this->expectsEvents('cart.added');

        $cart = $this->getCart();

        $item1 = $this->getBuyableMock();
        $item2 = $this->getBuyableMock(2);

        $cart->add([$item1, $item2]);

        $this->assertEquals(2, $cart->count());
    }

    /** @test */
    public function it_will_return_an_array_of_cartitems_when_you_add_multiple_items_at_once()
    {
        $this->expectsEvents('cart.added');

        $cart = $this->getCart();

        $item1 = $this->getBuyableMock();
        $item2 = $this->getBuyableMock(2);

        $cartItems = $cart->add([$item1, $item2]);

        $this->assertTrue(is_array($cartItems));
        $this->assertCount(2, $cartItems);
        $this->assertContainsOnlyInstancesOf(\Gloudemans\Shoppingcart\CartItem::class, $cartItems);
    }

    /** @test */
    public function it_can_add_an_item_from_attributes()
    {
        $this->expectsEvents('cart.added');

        $cart = $this->getCart();

        $cart->add(1, 'Test item', 1, 10.00);

        $this->assertEquals(1, $cart->count());
    }

    /** @test */
    public function it_can_add_an_item_from_an_array()
    {
        $this->expectsEvents('cart.added');

        $cart = $this->getCart();

        $cart->add(['id' => 1, 'name' => 'Test item', 'qty' => 1, 'price' => 10.00]);

        $this->assertEquals(1, $cart->count());
    }

    /** @test */
    public function it_can_add_multiple_array_items_at_once()
    {
        $this->expectsEvents('cart.added');

        $cart = $this->getCart();

        $cart->add([
            ['id' => 1, 'name' => 'Test item 1', 'qty' => 1, 'price' => 10.00],
            ['id' => 2, 'name' => 'Test item 2', 'qty' => 1, 'price' => 10.00]
        ]);

        $this->assertEquals(2, $cart->count());
    }

    /** @test */
    public function it_can_add_an_item_with_options()
    {
        $this->expectsEvents('cart.added');

        $cart = $this->getCart();

        $item = $this->getBuyableMock();

        $options = ['size' => 'XL', 'color' => 'red'];

        $cart->add($item, 1, $options);

        $cartItem = $cart->get('07d5da5550494c62daf9993cf954303f');

        $this->assertInstanceOf(\Gloudemans\Shoppingcart\CartItem::class, $cartItem);
        $this->assertEquals('XL', $cartItem->options->size);
        $this->assertEquals('red', $cartItem->options->color);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Please supply a valid identifier.
     */
    public function it_will_validate_the_identifier()
    {
        $cart = $this->getCart();

        $cart->add(null, 'Some title', 1, 10.00);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Please supply a valid name.
     */
    public function it_will_validate_the_name()
    {
        $cart = $this->getCart();

        $cart->add(1, null, 1, 10.00);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Please supply a valid quantity.
     */
    public function it_will_validate_the_quantity()
    {
        $cart = $this->getCart();

        $cart->add(1, 'Some title', 'invalid', 10.00);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Please supply a valid price.
     */
    public function it_will_validate_the_price()
    {
        $cart = $this->getCart();

        $cart->add(1, 'Some title', 1, 'invalid');
    }

    /** @test */
    public function it_will_update_the_cart_if_the_item_already_exists_in_the_cart()
    {
        $cart = $this->getCart();

        $item = $this->getBuyableMock();

        $cart->add($item);
        $cart->add($item);

        $this->assertItemsInCart(2, $cart);
        $this->assertRowsInCart(1, $cart);
    }

    /** @test */
    public function it_will_keep_updating_the_quantity_when_an_item_is_added_multiple_times()
    {
        $cart = $this->getCart();

        $item = $this->getBuyableMock();

        $cart->add($item);
        $cart->add($item);
        $cart->add($item);

        $this->assertItemsInCart(3, $cart);
        $this->assertRowsInCart(1, $cart);
    }

    /** @test */
    public function it_can_update_the_quantity_of_an_existing_item_in_the_cart()
    {
        $this->expectsEvents('cart.updated');

        $cart = $this->getCart();

        $item = $this->getBuyableMock();

        $cart->add($item);

        $cart->update('027c91341fd5cf4d2579b49c4b6a90da', 2);

        $this->assertItemsInCart(2, $cart);
        $this->assertRowsInCart(1, $cart);
    }

    /** @test */
    public function it_can_update_an_existing_item_in_the_cart_from_a_buyable()
    {
        $this->expectsEvents('cart.updated');

        $cart = $this->getCart();

        $item = $this->getBuyableMock();

        $cart->add($item);

        $item2 = $this->getBuyableMock(1, 'Different description');

        $cart->update('027c91341fd5cf4d2579b49c4b6a90da', $item2);

        $this->assertItemsInCart(1, $cart);
        $this->assertEquals('Different description', $cart->get('027c91341fd5cf4d2579b49c4b6a90da')->name);
    }

    /** @test */
    public function it_can_update_an_existing_item_in_the_cart_from_an_array()
    {
        $this->expectsEvents('cart.updated');

        $cart = $this->getCart();

        $item = $this->getBuyableMock();

        $cart->add($item);

        $cart->update('027c91341fd5cf4d2579b49c4b6a90da', ['name' => 'Different description']);

        $this->assertItemsInCart(1, $cart);
        $this->assertEquals('Different description', $cart->get('027c91341fd5cf4d2579b49c4b6a90da')->name);
    }

    /**
     * @test
     * @expectedException \Gloudemans\Shoppingcart\Exceptions\InvalidRowIDException
     */
    public function it_will_throw_an_exception_if_a_rowid_was_not_found()
    {
        $cart = $this->getCart();

        $item = $this->getBuyableMock();

        $cart->add($item);

        $item2 = $this->getBuyableMock(1, 'Different description');

        $cart->update('none-existing-rowid', $item2);
    }

    /** @test */
    public function it_will_regenerate_the_rowid_if_the_options_changed()
    {
        $cart = $this->getCart();

        $item = $this->getBuyableMock();

        $cart->add($item, 1, ['color' => 'red']);

        $cart->update('ea65e0bdcd1967c4b3149e9e780177c0', ['options' => ['color' => 'blue']]);

        $this->assertItemsInCart(1, $cart);
        $this->assertEquals('7e70a1e9aaadd18c72921a07aae5d011', $cart->content()->first()->rowId);
        $this->assertEquals('blue', $cart->get('7e70a1e9aaadd18c72921a07aae5d011')->options->color);
    }

    /** @test */
    public function it_will_add_the_item_to_an_existing_row_if_the_options_changed_to_an_existing_rowid()
    {
        $cart = $this->getCart();

        $item1 = $this->getBuyableMock();
        $item2 = $this->getBuyableMock();

        $cart->add($item1, 1, ['color' => 'red']);
        $cart->add($item2, 1, ['color' => 'blue']);

        $cart->update('7e70a1e9aaadd18c72921a07aae5d011', ['options' => ['color' => 'red']]);

        $this->assertItemsInCart(2, $cart);
        $this->assertRowsInCart(1, $cart);
    }

    /** @test */
    public function it_can_remove_an_item_from_the_cart()
    {
        $this->expectsEvents('cart.removed');

        $cart = $this->getCart();

        $item = $this->getBuyableMock();

        $cart->add($item);

        $cart->remove('027c91341fd5cf4d2579b49c4b6a90da');

        $this->assertItemsInCart(0, $cart);
        $this->assertRowsInCart(0, $cart);
    }

    /** @test */
    public function it_will_remove_the_item_if_its_quantity_was_set_to_zero()
    {
        $this->expectsEvents('cart.removed');

        $cart = $this->getCart();

        $item = $this->getBuyableMock();

        $cart->add($item);

        $cart->update('027c91341fd5cf4d2579b49c4b6a90da', 0);

        $this->assertItemsInCart(0, $cart);
        $this->assertRowsInCart(0, $cart);
    }

    /** @test */
    public function it_will_remove_the_item_if_its_quantity_was_set_negative()
    {
        $this->expectsEvents('cart.removed');

        $cart = $this->getCart();

        $item = $this->getBuyableMock();

        $cart->add($item);

        $cart->update('027c91341fd5cf4d2579b49c4b6a90da', -1);

        $this->assertItemsInCart(0, $cart);
        $this->assertRowsInCart(0, $cart);
    }

    /** @test */
    public function it_can_get_an_item_from_the_cart_by_its_rowid()
    {
        $cart = $this->getCart();

        $item = $this->getBuyableMock();

        $cart->add($item);

        $cartItem = $cart->get('027c91341fd5cf4d2579b49c4b6a90da');

        $this->assertInstanceOf(\Gloudemans\Shoppingcart\CartItem::class, $cartItem);
    }

    /** @test */
    public function it_can_get_the_content_of_the_cart()
    {
        $cart = $this->getCart();

        $item = $this->getBuyableMock();
        $item2 = $this->getBuyableMock(2);

        $cart->add($item);
        $cart->add($item2);

        $content = $cart->content();

        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $content);
        $this->assertCount(2, $content);
    }

    /** @test */
    public function it_will_return_an_empty_collection_if_the_cart_is_empty()
    {
        $cart = $this->getCart();

        $content = $cart->content();

        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $content);
        $this->assertCount(0, $content);
    }

    /** @test */
    public function it_will_include_the_tax_and_subtotal_when_converted_to_an_array()
    {
        $cart = $this->getCart();

        $item = $this->getBuyableMock();
        $item2 = $this->getBuyableMock(2);

        $cart->add($item);
        $cart->add($item2);

        $content = $cart->content();

        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $content);
        $this->assertEquals([
            '027c91341fd5cf4d2579b49c4b6a90da' => [
                'rowId' => '027c91341fd5cf4d2579b49c4b6a90da',
                'id' => 1,
                'name' => 'Item name',
                'qty' => 1,
                'price' => 10.00,
                'tax' => 2.10,
                'subtotal' => 10.0,
            ],
            '370d08585360f5c568b18d1f2e4ca1df' => [
                'rowId' => '370d08585360f5c568b18d1f2e4ca1df',
                'id' => 2,
                'name' => 'Item name',
                'qty' => 1,
                'price' => 10.00,
                'tax' => 2.10,
                'subtotal' => 10.0,
            ]
        ], $content->toArray());
    }

    /** @test */
    public function it_can_destroy_a_cart()
    {
        $cart = $this->getCart();

        $item = $this->getBuyableMock();

        $cart->add($item);

        $this->assertItemsInCart(1, $cart);

        $cart->destroy();

        $this->assertItemsInCart(0, $cart);
    }

    /** @test */
    public function it_can_get_the_total_price_of_the_cart_content()
    {
        $cart = $this->getCart();

        $item = $this->getBuyableMock(1, 'First item', 10.00);
        $item2 = $this->getBuyableMock(2, 'Second item', 25.00);

        $cart->add($item);
        $cart->add($item2, 2);

        $this->assertItemsInCart(3, $cart);
        $this->assertEquals(60.00, $cart->total);
    }

    /** @test */
    public function it_can_return_a_formatted_total()
    {
        $cart = $this->getCart();

        $item = $this->getBuyableMock(1, 'First item', 1000.00);
        $item2 = $this->getBuyableMock(2, 'Second item', 2500.00);

        $cart->add($item);
        $cart->add($item2, 2);

        $this->assertItemsInCart(3, $cart);
        $this->assertEquals('6.000,00', $cart->total(2, ',', '.'));
    }

    /** @test */
    public function it_can_search_the_cart_for_a_specific_item()
    {
        $cart = $this->getCart();

        $item = $this->getBuyableMock(1, 'Some item');
        $item2 = $this->getBuyableMock(2, 'Another item');

        $cart->add($item);
        $cart->add($item2);

        $cartItem = $cart->search(function ($cartItem, $rowId) {
            return $cartItem->name == 'Some item';
        });

        $this->assertInstanceOf(\Gloudemans\Shoppingcart\CartItem::class, $cartItem);
        $this->assertEquals(1, $cartItem->id);
    }

    /** @test */
    public function it_can_search_the_cart_for_multiple_items()
    {
        $cart = $this->getCart();

        $item = $this->getBuyableMock(1, 'Some item');
        $item2 = $this->getBuyableMock(2, 'Some item');
        $item3 = $this->getBuyableMock(3, 'Another item');

        $cart->add($item);
        $cart->add($item2);
        $cart->add($item3);

        $cartItem = $cart->search(function ($cartItem, $rowId) {
            return $cartItem->name == 'Some item';
        });

        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $cartItem);
    }

    /** @test */
    public function it_can_search_the_cart_for_a_specific_item_with_options()
    {
        $cart = $this->getCart();

        $item = $this->getBuyableMock(1, 'Some item');
        $item2 = $this->getBuyableMock(2, 'Another item');

        $cart->add($item, 1, ['color' => 'red']);
        $cart->add($item2, 1, ['color' => 'blue']);

        $cartItem = $cart->search(function ($cartItem, $rowId) {
            return $cartItem->options->color == 'red';
        });

        $this->assertInstanceOf(\Gloudemans\Shoppingcart\CartItem::class, $cartItem);
        $this->assertEquals(1, $cartItem->id);
    }

    /** @test */
    public function it_will_associate_the_cart_item_with_a_model_when_you_add_a_buyable()
    {
        $cart = $this->getCart();

        $item = $this->getBuyableMock();

        $cart->add($item);

        $cartItem = $cart->get('027c91341fd5cf4d2579b49c4b6a90da');

        $this->assertEquals('Mockery_0_Gloudemans_Shoppingcart_Contracts_Buyable', PHPUnit_Framework_Assert::readAttribute($cartItem, 'associatedModel'));
    }

    /** @test */
    public function it_can_associate_the_cart_item_with_a_model()
    {
        $cart = $this->getCart();

        $model = Mockery::mock('MockModel');

        $cart->add(1, 'Test item', 1, 10.00);

        $cart->associate('027c91341fd5cf4d2579b49c4b6a90da', $model);

        $cartItem = $cart->get('027c91341fd5cf4d2579b49c4b6a90da');

        $this->assertEquals(get_class($model), PHPUnit_Framework_Assert::readAttribute($cartItem, 'associatedModel'));
    }

    /**
     * @test
     * @expectedException \Gloudemans\Shoppingcart\Exceptions\UnknownModelException
     * @expectedExceptionMessage The supplied model SomeModel does not exist.
     */
    public function it_will_throw_an_exception_when_a_non_existing_model_is_being_associated()
    {
        $cart = $this->getCart();

        $cart->add(1, 'Test item', 1, 10.00);

        $cart->associate('027c91341fd5cf4d2579b49c4b6a90da', 'SomeModel');
    }

    /** @test */
    public function it_can_get_the_associated_model_of_a_cart_item()
    {
        $cart = $this->getCart();

        $model = new ModelStub;

        $cart->add(1, 'Test item', 1, 10.00);

        $cart->associate('027c91341fd5cf4d2579b49c4b6a90da', $model);

        $cartItem = $cart->get('027c91341fd5cf4d2579b49c4b6a90da');

        $this->assertInstanceOf(ModelStub::class, $cartItem->model);
        $this->assertEquals('Some value', $cartItem->model->someValue);
    }

    /** @test */
    public function it_can_calculate_the_subtotal_of_a_cart_item()
    {
        $cart = $this->getCart();

        $item = $this->getBuyableMock(1, 'Some title', 9.99);

        $cart->add($item, 3);

        $cartItem = $cart->get('027c91341fd5cf4d2579b49c4b6a90da');

        $this->assertEquals(29.97, $cartItem->subtotal);
    }

    /** @test */
    public function it_can_return_a_formatted_subtotal()
    {
        $cart = $this->getCart();

        $item = $this->getBuyableMock(1, 'Some title', 500);

        $cart->add($item, 3);

        $cartItem = $cart->get('027c91341fd5cf4d2579b49c4b6a90da');

        $this->assertEquals('1.500,00', $cartItem->subtotal(2, ',', '.'));
    }

    /** @test */
    public function it_can_calculate_tax_based_on_the_default_tax_rate_in_the_config()
    {
        $cart = $this->getCart();

        $item = $this->getBuyableMock(1, 'Some title', 10.00);

        $cart->add($item, 1);

        $cartItem = $cart->get('027c91341fd5cf4d2579b49c4b6a90da');

        $this->assertEquals(2.10, $cartItem->tax);
    }

    /** @test */
    public function it_can_calculate_tax_based_on_the_specified_tax()
    {
        $cart = $this->getCart();

        $item = $this->getBuyableMock(1, 'Some title', 10.00);

        $cart->add($item, 1);

        $cart->setTax('027c91341fd5cf4d2579b49c4b6a90da', 19);

        $cartItem = $cart->get('027c91341fd5cf4d2579b49c4b6a90da');

        $this->assertEquals(1.90, $cartItem->tax);
    }

    /** @test */
    public function it_can_return_the_calculated_tax_formatted()
    {
        $cart = $this->getCart();

        $item = $this->getBuyableMock(1, 'Some title', 10000.00);

        $cart->add($item, 1);

        $cartItem = $cart->get('027c91341fd5cf4d2579b49c4b6a90da');

        $this->assertEquals('2.100,00', $cartItem->tax(2, ',', '.'));
    }

    /** @test */
    public function it_can_calculate_the_total_tax_for_all_cart_items()
    {
        $cart = $this->getCart();

        $item = $this->getBuyableMock(1, 'Some title', 10.00);
        $item2 = $this->getBuyableMock(2, 'Some title', 20.00);

        $cart->add($item, 1);
        $cart->add($item2, 2);

        $this->assertEquals(10.50, $cart->tax);
    }

    /** @test */
    public function it_can_return_formatted_total_tax()
    {
        $cart = $this->getCart();

        $item = $this->getBuyableMock(1, 'Some title', 1000.00);
        $item2 = $this->getBuyableMock(2, 'Some title', 2000.00);

        $cart->add($item, 1);
        $cart->add($item2, 2);

        $this->assertEquals('1.050,00', $cart->tax(2, ',', '.'));
    }

    /** @test */
    public function it_can_return_the_subtotal()
    {
        $cart = $this->getCart();

        $item = $this->getBuyableMock(1, 'Some title', 10.00);
        $item2 = $this->getBuyableMock(2, 'Some title', 20.00);

        $cart->add($item, 1);
        $cart->add($item2, 2);

        $this->assertEquals(39.50, $cart->subtotal);
    }

    /** @test */
    public function it_can_return_formatted_subtotal()
    {
        $cart = $this->getCart();

        $item = $this->getBuyableMock(1, 'Some title', 1000.00);
        $item2 = $this->getBuyableMock(2, 'Some title', 2000.00);

        $cart->add($item, 1);
        $cart->add($item2, 2);

        $this->assertEquals('3.950,00', $cart->subtotal(2, ',', '.'));
    }

    /** @test */
    public function it_can_store_the_cart_in_a_database()
    {
        $this->artisan('migrate', [
            '--database' => 'testing',
            '--realpath' => realpath(__DIR__.'/../database/migrations'),
        ]);

        $this->expectsEvents('cart.stored');

        $cart = $this->getCart();

        $item = $this->getBuyableMock();

        $cart->add($item);

        $cart->store($identifier = 123);

        $serialized = serialize($cart->content());

        $this->seeInDatabase('shoppingcart', ['identifier' => $identifier, 'instance' => 'default', 'content' => $serialized]);
    }

    /** 
     * @test
     * @expectedException \Gloudemans\Shoppingcart\Exceptions\CartAlreadyStoredException
     * @expectedExceptionMessage A cart with identifier 123 was already stored.
     */
    public function it_will_throw_an_exception_when_a_cart_was_already_stored_using_the_specified_identifier()
    {
        $this->artisan('migrate', [
            '--database' => 'testing',
            '--realpath' => realpath(__DIR__.'/../database/migrations'),
        ]);

        $this->expectsEvents('cart.stored');

        $cart = $this->getCart();

        $item = $this->getBuyableMock();

        $cart->add($item);

        $cart->store($identifier = 123);

        $cart->store($identifier);
    }

    /** @test */
    public function it_can_restore_a_cart_from_the_database()
    {
        $this->artisan('migrate', [
            '--database' => 'testing',
            '--realpath' => realpath(__DIR__.'/../database/migrations'),
        ]);

        $this->expectsEvents('cart.restored');

        $cart = $this->getCart();

        $item = $this->getBuyableMock();

        $cart->add($item);

        $cart->store($identifier = 123);

        $cart->destroy();

        $this->assertItemsInCart(0, $cart);

        $cart->restore($identifier);

        $this->assertItemsInCart(1, $cart);

        $this->dontSeeInDatabase('shoppingcart', ['identifier' => $identifier, 'instance' => 'default']);
    }

    /** @test */
    public function it_will_just_keep_the_current_instance_if_no_cart_with_the_given_identifier_was_stored()
    {
        $this->artisan('migrate', [
            '--database' => 'testing',
            '--realpath' => realpath(__DIR__.'/../database/migrations'),
        ]);

        $cart = $this->getCart();

        $cart->restore($identifier = 123);

        $this->assertItemsInCart(0, $cart);
    }

    /**
     * Get an instance of the cart.
     *
     * @return \Gloudemans\Shoppingcart\Cart
     */
    private function getCart()
    {
        $session = $this->app->make('session');
        $events = $this->app->make('events');

        $cart = new Cart($session, $events);

        return $cart;
    }

    /**
     * Get a mock of a Buyable item.
     *
     * @param int    $id
     * @param string $name
     * @param float  $price
     * @return \Mockery\MockInterface
     */
    private function getBuyableMock($id = 1, $name = 'Item name', $price = 10.00)
    {
        $item = Mockery::mock(Buyable::class)->shouldIgnoreMissing();

        $item->shouldReceive('getBuyableIdentifier')->andReturn($id);
        $item->shouldReceive('getBuyableDescription')->andReturn($name);
        $item->shouldReceive('getBuyablePrice')->andReturn($price);

        return $item;
    }
}

class ModelStub {
    public $someValue = 'Some value';
    public function find($id) { return $this; }
}