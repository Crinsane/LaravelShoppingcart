<?php

namespace Gloudemans\Tests\Shoppingcart;

use Gloudemans\Shoppingcart\Cart;
use Gloudemans\Shoppingcart\CartItem;
use Gloudemans\Shoppingcart\ShoppingcartServiceProvider;
use Gloudemans\Tests\Shoppingcart\Fixtures\BuyableProduct;
use Gloudemans\Tests\Shoppingcart\Fixtures\BuyableProductTrait;
use Gloudemans\Tests\Shoppingcart\Fixtures\Identifiable;
use Gloudemans\Tests\Shoppingcart\Fixtures\ProductModel;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Session\SessionManager;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Mockery;
use Orchestra\Testbench\TestCase;

class CartTest extends TestCase
{
    use CartAssertions;

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

    /**
     * Define environment setup.
     *
     * @param \Illuminate\Foundation\Application $app
     *
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

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->app->afterResolving('migrator', function ($migrator) {
            $migrator->path(realpath(__DIR__.'/../src/Database/migrations'));
        });
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

        $cart->add(new BuyableProduct(1, 'First item'));

        $cart->instance('wishlist')->add(new BuyableProduct(2, 'Second item'));

        $this->assertItemsInCart(1, $cart->instance(Cart::DEFAULT_INSTANCE));
        $this->assertItemsInCart(1, $cart->instance('wishlist'));
    }

    /** @test */
    public function it_can_add_an_item()
    {
        Event::fake();

        $cart = $this->getCart();

        $cart->add(new BuyableProduct());

        $this->assertEquals(1, $cart->count());

        Event::assertDispatched('cart.added');
    }

    /** @test */
    public function it_will_return_the_cartitem_of_the_added_item()
    {
        Event::fake();

        $cart = $this->getCart();

        $cartItem = $cart->add(new BuyableProduct());

        $this->assertInstanceOf(CartItem::class, $cartItem);
        $this->assertEquals('027c91341fd5cf4d2579b49c4b6a90da', $cartItem->rowId);

        Event::assertDispatched('cart.added');
    }

    /** @test */
    public function it_can_add_multiple_buyable_items_at_once()
    {
        Event::fake();

        $cart = $this->getCart();

        $cart->add([new BuyableProduct(1), new BuyableProduct(2)]);

        $this->assertEquals(2, $cart->count());

        Event::assertDispatched('cart.added');
    }

    /** @test */
    public function it_will_return_an_array_of_cartitems_when_you_add_multiple_items_at_once()
    {
        Event::fake();

        $cart = $this->getCart();

        $cartItems = $cart->add([new BuyableProduct(1), new BuyableProduct(2)]);

        $this->assertTrue(is_array($cartItems));
        $this->assertCount(2, $cartItems);
        $this->assertContainsOnlyInstancesOf(CartItem::class, $cartItems);

        Event::assertDispatched('cart.added');
    }

    /** @test */
    public function it_can_add_an_item_from_attributes()
    {
        Event::fake();

        $cart = $this->getCart();

        $cart->add(1, 'Test item', 1, 10.00);

        $this->assertEquals(1, $cart->count());

        Event::assertDispatched('cart.added');
    }

    /** @test */
    public function it_can_add_an_item_from_an_array()
    {
        Event::fake();

        $cart = $this->getCart();

        $cart->add(['id' => 1, 'name' => 'Test item', 'qty' => 1, 'price' => 10.00, 'weight' => 550]);

        $this->assertEquals(1, $cart->count());

        Event::assertDispatched('cart.added');
    }

    /** @test */
    public function it_can_add_multiple_array_items_at_once()
    {
        Event::fake();

        $cart = $this->getCart();

        $cart->add([
            ['id' => 1, 'name' => 'Test item 1', 'qty' => 1, 'price' => 10.00, 'weight' => 550],
            ['id' => 2, 'name' => 'Test item 2', 'qty' => 1, 'price' => 10.00, 'weight' => 550],
        ]);

        $this->assertEquals(2, $cart->count());

        Event::assertDispatched('cart.added');
    }

    /** @test */
    public function it_can_add_an_item_with_options()
    {
        Event::fake();

        $cart = $this->getCart();

        $options = ['size' => 'XL', 'color' => 'red'];

        $cart->add(new BuyableProduct(), 1, $options);

        $cartItem = $cart->get('07d5da5550494c62daf9993cf954303f');

        $this->assertInstanceOf(CartItem::class, $cartItem);
        $this->assertEquals('XL', $cartItem->options->size);
        $this->assertEquals('red', $cartItem->options->color);

        Event::assertDispatched('cart.added');
    }

    /**
     * @test
     */
    public function it_will_validate_the_identifier()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Please supply a valid identifier.');

        $cart = $this->getCart();

        $cart->add(null, 'Some title', 1, 10.00);
    }

    /**
     * @test
     */
    public function it_will_validate_the_name()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Please supply a valid name.');

        $cart = $this->getCart();

        $cart->add(1, null, 1, 10.00);
    }

    /**
     * @test
     */
    public function it_will_validate_the_quantity()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Please supply a valid quantity.');

        $cart = $this->getCart();

        $cart->add(1, 'Some title', 'invalid', 10.00);
    }

    /**
     * @test
     */
    public function it_will_validate_the_price()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Please supply a valid price.');

        $cart = $this->getCart();

        $cart->add(1, 'Some title', 1, 'invalid');
    }

    /**
     * @test
     */
    public function it_will_validate_the_weight()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Please supply a valid weight');

        $cart = $this->getCart();

        $cart->add(1, 'Some title', 1, 10.00, 'invalid');
    }

    /** @test */
    public function it_will_update_the_cart_if_the_item_already_exists_in_the_cart()
    {
        $cart = $this->getCart();

        $item = new BuyableProduct();

        $cart->add($item);
        $cart->add($item);

        $this->assertItemsInCart(2, $cart);
        $this->assertRowsInCart(1, $cart);
    }

    /** @test */
    public function it_will_keep_updating_the_quantity_when_an_item_is_added_multiple_times()
    {
        $cart = $this->getCart();

        $item = new BuyableProduct();

        $cart->add($item);
        $cart->add($item);
        $cart->add($item);

        $this->assertItemsInCart(3, $cart);
        $this->assertRowsInCart(1, $cart);
    }

    /** @test */
    public function it_can_update_the_quantity_of_an_existing_item_in_the_cart()
    {
        Event::fake();

        $cart = $this->getCart();

        $cart->add(new BuyableProduct());

        $cart->update('027c91341fd5cf4d2579b49c4b6a90da', 2);

        $this->assertItemsInCart(2, $cart);
        $this->assertRowsInCart(1, $cart);

        Event::assertDispatched('cart.updated');
    }

    /** @test */
    public function it_can_update_an_existing_item_in_the_cart_from_a_buyable()
    {
        Event::fake();

        $cart = $this->getCart();

        $cart->add(new BuyableProduct());

        $cart->update('027c91341fd5cf4d2579b49c4b6a90da', new BuyableProduct(1, 'Different description'));

        $this->assertItemsInCart(1, $cart);
        $this->assertEquals('Different description', $cart->get('027c91341fd5cf4d2579b49c4b6a90da')->name);

        Event::assertDispatched('cart.updated');
    }

    /** @test */
    public function it_can_update_an_existing_item_in_the_cart_from_an_array()
    {
        Event::fake();

        $cart = $this->getCart();

        $cart->add(new BuyableProduct());

        $cart->update('027c91341fd5cf4d2579b49c4b6a90da', ['name' => 'Different description']);

        $this->assertItemsInCart(1, $cart);
        $this->assertEquals('Different description', $cart->get('027c91341fd5cf4d2579b49c4b6a90da')->name);

        Event::assertDispatched('cart.updated');
    }

    /**
     * @test
     */
    public function it_will_throw_an_exception_if_a_rowid_was_not_found()
    {
        $this->expectException(\Gloudemans\Shoppingcart\Exceptions\InvalidRowIDException::class);

        $cart = $this->getCart();

        $cart->add(new BuyableProduct());

        $cart->update('none-existing-rowid', new BuyableProduct(1, 'Different description'));
    }

    /** @test */
    public function it_will_regenerate_the_rowid_if_the_options_changed()
    {
        $cart = $this->getCart();

        $cart->add(new BuyableProduct(), 1, ['color' => 'red']);

        $cart->update('ea65e0bdcd1967c4b3149e9e780177c0', ['options' => ['color' => 'blue']]);

        $this->assertItemsInCart(1, $cart);
        $this->assertEquals('7e70a1e9aaadd18c72921a07aae5d011', $cart->content()->first()->rowId);
        $this->assertEquals('blue', $cart->get('7e70a1e9aaadd18c72921a07aae5d011')->options->color);
    }

    /** @test */
    public function it_will_add_the_item_to_an_existing_row_if_the_options_changed_to_an_existing_rowid()
    {
        $cart = $this->getCart();

        $cart->add(new BuyableProduct(), 1, ['color' => 'red']);
        $cart->add(new BuyableProduct(), 1, ['color' => 'blue']);

        $cart->update('7e70a1e9aaadd18c72921a07aae5d011', ['options' => ['color' => 'red']]);

        $this->assertItemsInCart(2, $cart);
        $this->assertRowsInCart(1, $cart);
    }

    /** @test */
    public function it_can_remove_an_item_from_the_cart()
    {
        Event::fake();

        $cart = $this->getCart();

        $cart->add(new BuyableProduct());

        $cart->remove('027c91341fd5cf4d2579b49c4b6a90da');

        $this->assertItemsInCart(0, $cart);
        $this->assertRowsInCart(0, $cart);

        Event::assertDispatched('cart.removed');
    }

    /** @test */
    public function it_will_remove_the_item_if_its_quantity_was_set_to_zero()
    {
        Event::fake();

        $cart = $this->getCart();

        $cart->add(new BuyableProduct());

        $cart->update('027c91341fd5cf4d2579b49c4b6a90da', 0);

        $this->assertItemsInCart(0, $cart);
        $this->assertRowsInCart(0, $cart);

        Event::assertDispatched('cart.removed');
    }

    /** @test */
    public function it_will_remove_the_item_if_its_quantity_was_set_negative()
    {
        Event::fake();

        $cart = $this->getCart();

        $cart->add(new BuyableProduct());

        $cart->update('027c91341fd5cf4d2579b49c4b6a90da', -1);

        $this->assertItemsInCart(0, $cart);
        $this->assertRowsInCart(0, $cart);

        Event::assertDispatched('cart.removed');
    }

    /** @test */
    public function it_can_get_an_item_from_the_cart_by_its_rowid()
    {
        $cart = $this->getCart();

        $cart->add(new BuyableProduct());

        $cartItem = $cart->get('027c91341fd5cf4d2579b49c4b6a90da');

        $this->assertInstanceOf(CartItem::class, $cartItem);
    }

    /** @test */
    public function it_can_get_the_content_of_the_cart()
    {
        $cart = $this->getCart();

        $cart->add(new BuyableProduct(1));
        $cart->add(new BuyableProduct(2));

        $content = $cart->content();

        $this->assertInstanceOf(Collection::class, $content);
        $this->assertCount(2, $content);
    }

    /** @test */
    public function it_will_return_an_empty_collection_if_the_cart_is_empty()
    {
        $cart = $this->getCart();

        $content = $cart->content();

        $this->assertInstanceOf(Collection::class, $content);
        $this->assertCount(0, $content);
    }

    /** @test */
    public function it_will_include_the_tax_and_subtotal_when_converted_to_an_array()
    {
        $cart = $this->getCart();

        $cart->add(new BuyableProduct(1));
        $cart->add(new BuyableProduct(2));

        $content = $cart->content();

        $this->assertInstanceOf(Collection::class, $content);
        $this->assertEquals([
            '027c91341fd5cf4d2579b49c4b6a90da' => [
                'rowId'    => '027c91341fd5cf4d2579b49c4b6a90da',
                'id'       => 1,
                'name'     => 'Item name',
                'qty'      => 1,
                'price'    => 10.00,
                'tax'      => 2.10,
                'subtotal' => 10.0,
                'options'  => [],
                'discount' => 0.0,
                'weight'   => 0.0,
            ],
            '370d08585360f5c568b18d1f2e4ca1df' => [
                'rowId'    => '370d08585360f5c568b18d1f2e4ca1df',
                'id'       => 2,
                'name'     => 'Item name',
                'qty'      => 1,
                'price'    => 10.00,
                'tax'      => 2.10,
                'subtotal' => 10.0,
                'options'  => [],
                'discount' => 0.0,
                'weight'   => 0.0,
            ],
        ], $content->toArray());
    }

    /** @test */
    public function it_can_destroy_a_cart()
    {
        $cart = $this->getCart();

        $cart->add(new BuyableProduct());

        $this->assertItemsInCart(1, $cart);

        $cart->destroy();

        $this->assertItemsInCart(0, $cart);
    }

    /** @test */
    public function it_can_get_the_total_price_of_the_cart_content()
    {
        $cart = $this->getCart();

        $cart->add(new BuyableProduct(1, 'First item', 10.00));
        $cart->add(new BuyableProduct(2, 'Second item', 25.00), 2);

        $this->assertItemsInCart(3, $cart);
        $this->assertEquals(60.00, $cart->subtotal());
    }

    /** @test */
    public function it_can_return_a_formatted_total()
    {
        $cart = $this->getCart();

        $cart->add(new BuyableProduct(1, 'First item', 1000.00));
        $cart->add(new BuyableProduct(2, 'Second item', 2500.00), 2);

        $this->assertItemsInCart(3, $cart);
        $this->assertEquals('6.000,00', $cart->subtotal(2, ',', '.'));
    }

    /** @test */
    public function it_can_search_the_cart_for_a_specific_item()
    {
        $cart = $this->getCart();

        $cart->add(new BuyableProduct(1, 'Some item'));
        $cart->add(new BuyableProduct(2, 'Another item'));

        $cartItem = $cart->search(function ($cartItem, $rowId) {
            return $cartItem->name == 'Some item';
        });

        $this->assertInstanceOf(Collection::class, $cartItem);
        $this->assertCount(1, $cartItem);
        $this->assertInstanceOf(CartItem::class, $cartItem->first());
        $this->assertEquals(1, $cartItem->first()->id);
    }

    /** @test */
    public function it_can_search_the_cart_for_multiple_items()
    {
        $cart = $this->getCart();

        $cart->add(new BuyableProduct(1, 'Some item'));
        $cart->add(new BuyableProduct(2, 'Some item'));
        $cart->add(new BuyableProduct(3, 'Another item'));

        $cartItem = $cart->search(function ($cartItem, $rowId) {
            return $cartItem->name == 'Some item';
        });

        $this->assertInstanceOf(Collection::class, $cartItem);
    }

    /** @test */
    public function it_can_search_the_cart_for_a_specific_item_with_options()
    {
        $cart = $this->getCart();

        $cart->add(new BuyableProduct(1, 'Some item'), 1, ['color' => 'red']);
        $cart->add(new BuyableProduct(2, 'Another item'), 1, ['color' => 'blue']);

        $cartItem = $cart->search(function ($cartItem, $rowId) {
            return $cartItem->options->color == 'red';
        });

        $this->assertInstanceOf(Collection::class, $cartItem);
        $this->assertCount(1, $cartItem);
        $this->assertInstanceOf(CartItem::class, $cartItem->first());
        $this->assertEquals(1, $cartItem->first()->id);
    }

    /** @test */
    public function it_will_associate_the_cart_item_with_a_model_when_you_add_a_buyable()
    {
        $cart = $this->getCart();

        $cart->add(new BuyableProduct());

        $cartItem = $cart->get('027c91341fd5cf4d2579b49c4b6a90da');

        $this->assertEquals(BuyableProduct::class, $cartItem->modelFQCN);
    }

    /** @test */
    public function it_can_associate_the_cart_item_with_a_model()
    {
        $cart = $this->getCart();

        $cart->add(1, 'Test item', 1, 10.00);

        $cart->associate('027c91341fd5cf4d2579b49c4b6a90da', new ProductModel());

        $cartItem = $cart->get('027c91341fd5cf4d2579b49c4b6a90da');

        $this->assertEquals(ProductModel::class, $cartItem->modelFQCN);
    }

    /**
     * @test
     */
    public function it_will_throw_an_exception_when_a_non_existing_model_is_being_associated()
    {
        $this->expectException(\Gloudemans\Shoppingcart\Exceptions\UnknownModelException::class);
        $this->expectExceptionMessage('The supplied model SomeModel does not exist.');

        $cart = $this->getCart();

        $cart->add(1, 'Test item', 1, 10.00);

        $cart->associate('027c91341fd5cf4d2579b49c4b6a90da', 'SomeModel');
    }

    /** @test */
    public function it_can_get_the_associated_model_of_a_cart_item()
    {
        $cart = $this->getCart();

        $cart->add(1, 'Test item', 1, 10.00);

        $cart->associate('027c91341fd5cf4d2579b49c4b6a90da', new ProductModel());

        $cartItem = $cart->get('027c91341fd5cf4d2579b49c4b6a90da');

        $this->assertInstanceOf(ProductModel::class, $cartItem->model);
        $this->assertEquals('Some value', $cartItem->model->someValue);
    }

    /** @test */
    public function it_can_calculate_the_subtotal_of_a_cart_item()
    {
        $cart = $this->getCart();

        $cart->add(new BuyableProduct(1, 'Some title', 9.99), 3);

        $cartItem = $cart->get('027c91341fd5cf4d2579b49c4b6a90da');

        $this->assertEquals(29.97, $cartItem->subtotal);
    }

    /** @test */
    public function it_can_return_a_formatted_subtotal()
    {
        $cart = $this->getCart();

        $cart->add(new BuyableProduct(1, 'Some title', 500), 3);

        $cartItem = $cart->get('027c91341fd5cf4d2579b49c4b6a90da');

        $this->assertEquals('1.500,00', $cartItem->subtotal(2, ',', '.'));
    }

    /** @test */
    public function it_can_calculate_tax_based_on_the_default_tax_rate_in_the_config()
    {
        $cart = $this->getCart();

        $cart->add(new BuyableProduct(1, 'Some title', 10.00), 1);

        $cartItem = $cart->get('027c91341fd5cf4d2579b49c4b6a90da');

        $this->assertEquals(2.10, $cartItem->tax);
    }

    /** @test */
    public function it_can_calculate_tax_based_on_the_specified_tax()
    {
        $cart = $this->getCart();

        $cart->add(new BuyableProduct(1, 'Some title', 10.00), 1);

        $cart->setTax('027c91341fd5cf4d2579b49c4b6a90da', 19);

        $cartItem = $cart->get('027c91341fd5cf4d2579b49c4b6a90da');

        $this->assertEquals(1.90, $cartItem->tax);
    }

    /** @test */
    public function it_can_return_the_calculated_tax_formatted()
    {
        $cart = $this->getCart();

        $cart->add(new BuyableProduct(1, 'Some title', 10000.00), 1);

        $cartItem = $cart->get('027c91341fd5cf4d2579b49c4b6a90da');

        $this->assertEquals('2.100,00', $cartItem->tax(2, ',', '.'));
    }

    /** @test */
    public function it_can_calculate_the_total_tax_for_all_cart_items()
    {
        $cart = $this->getCart();

        $cart->add(new BuyableProduct(1, 'Some title', 10.00), 1);
        $cart->add(new BuyableProduct(2, 'Some title', 20.00), 2);

        $this->assertEquals(10.50, $cart->tax);
    }

    /** @test */
    public function it_can_return_formatted_total_tax()
    {
        $cart = $this->getCart();

        $cart->add(new BuyableProduct(1, 'Some title', 1000.00), 1);
        $cart->add(new BuyableProduct(2, 'Some title', 2000.00), 2);

        $this->assertEquals('1.050,00', $cart->tax(2, ',', '.'));
    }

    /** @test */
    public function it_can_return_the_subtotal()
    {
        $cart = $this->getCart();

        $cart->add(new BuyableProduct(1, 'Some title', 10.00), 1);
        $cart->add(new BuyableProduct(2, 'Some title', 20.00), 2);

        $this->assertEquals(50.00, $cart->subtotal);
    }

    /** @test */
    public function it_can_return_formatted_subtotal()
    {
        $cart = $this->getCart();

        $cart->add(new BuyableProduct(1, 'Some title', 1000.00), 1);
        $cart->add(new BuyableProduct(2, 'Some title', 2000.00), 2);

        $this->assertEquals('5000,00', $cart->subtotal(2, ',', ''));
    }

    /** @test */
    public function it_can_return_cart_formated_numbers_by_config_values()
    {
        $this->setConfigFormat(2, ',', '');

        $cart = $this->getCart();

        $cart->add(new BuyableProduct(1, 'Some title', 1000.00), 1);
        $cart->add(new BuyableProduct(2, 'Some title', 2000.00), 2);

        $this->assertEquals('5000,00', $cart->subtotal());
        $this->assertEquals('1050,00', $cart->tax());
        $this->assertEquals('6050,00', $cart->total());

        $this->assertEquals('5000,00', $cart->subtotal);
        $this->assertEquals('1050,00', $cart->tax);
        $this->assertEquals('6050,00', $cart->total);
    }

    /** @test */
    public function it_can_return_cartItem_formated_numbers_by_config_values()
    {
        $this->setConfigFormat(2, ',', '');

        $cart = $this->getCartDiscount(50);

        $cart->add(new BuyableProduct(1, 'Some title', 2000.00), 2);

        $cartItem = $cart->get('027c91341fd5cf4d2579b49c4b6a90da');

        $this->assertEquals('2000,00', $cartItem->price());
        $this->assertEquals('1000,00', $cartItem->discount());
        $this->assertEquals('2000,00', $cartItem->discountTotal());
        $this->assertEquals('1000,00', $cartItem->priceTarget());
        $this->assertEquals('2000,00', $cartItem->subtotal());
        $this->assertEquals('210,00', $cartItem->tax());
        $this->assertEquals('420,00', $cartItem->taxTotal());
        $this->assertEquals('1210,00', $cartItem->priceTax());
        $this->assertEquals('2420,00', $cartItem->total());
    }

    /** @test */
    public function it_can_store_the_cart_in_a_database()
    {
        $this->artisan('migrate', [
            '--database' => 'testing',
        ]);

        Event::fake();

        $cart = $this->getCart();

        $cart->add(new BuyableProduct());

        $cart->store($identifier = 123);

        $serialized = serialize($cart->content());

        $this->assertDatabaseHas('shoppingcart', ['identifier' => $identifier, 'instance' => 'default', 'content' => $serialized]);

        Event::assertDispatched('cart.stored');
    }

    /**
     * @test
     */
    public function it_will_throw_an_exception_when_a_cart_was_already_stored_using_the_specified_identifier()
    {
        $this->expectException(\Gloudemans\Shoppingcart\Exceptions\CartAlreadyStoredException::class);
        $this->expectExceptionMessage('A cart with identifier 123 was already stored.');

        $this->artisan('migrate', [
            '--database' => 'testing',
        ]);

        Event::fake();

        $cart = $this->getCart();

        $cart->add(new BuyableProduct());

        $cart->store($identifier = 123);

        $cart->store($identifier);

        Event::assertDispatched('cart.stored');
    }

    /** @test */
    public function it_can_restore_a_cart_from_the_database()
    {
        $this->artisan('migrate', [
            '--database' => 'testing',
        ]);

        Event::fake();

        $cart = $this->getCart();

        $cart->add(new BuyableProduct());

        $cart->store($identifier = 123);

        $cart->destroy();

        $this->assertItemsInCart(0, $cart);

        $cart->restore($identifier);

        $this->assertItemsInCart(1, $cart);

        $this->assertDatabaseMissing('shoppingcart', ['identifier' => $identifier, 'instance' => 'default']);

        Event::assertDispatched('cart.restored');
    }

    /** @test */
    public function it_will_just_keep_the_current_instance_if_no_cart_with_the_given_identifier_was_stored()
    {
        $this->artisan('migrate', [
            '--database' => 'testing',
        ]);

        $cart = $this->getCart();

        $cart->restore($identifier = 123);

        $this->assertItemsInCart(0, $cart);
    }

    /** @test */
    public function it_can_calculate_all_values()
    {
        $cart = $this->getCartDiscount(50);

        $cart->add(new BuyableProduct(1, 'First item', 10.00), 2);

        $cartItem = $cart->get('027c91341fd5cf4d2579b49c4b6a90da');

        $cart->setTax('027c91341fd5cf4d2579b49c4b6a90da', 19);

        $this->assertEquals(10.00, $cartItem->price(2));
        $this->assertEquals(5.00, $cartItem->discount(2));
        $this->assertEquals(10.00, $cartItem->discountTotal(2));
        $this->assertEquals(5.00, $cartItem->priceTarget(2));
        $this->assertEquals(10.00, $cartItem->subtotal(2));
        $this->assertEquals(0.95, $cartItem->tax(2));
        $this->assertEquals(1.90, $cartItem->taxTotal(2));
        $this->assertEquals(5.95, $cartItem->priceTax(2));
        $this->assertEquals(11.90, $cartItem->total(2));
    }

    /** @test */
    public function it_will_destroy_the_cart_when_the_user_logs_out_and_the_config_setting_was_set_to_true()
    {
        $this->app['config']->set('cart.destroy_on_logout', true);

        $this->app->instance(SessionManager::class, Mockery::mock(SessionManager::class, function ($mock) {
            $mock->shouldReceive('forget')->once()->with('cart');
        }));

        $user = Mockery::mock(Authenticatable::class);

        \Auth::guard('web')->logout();
    }

    /** @test */
    public function can_change_tax_globally()
    {
        $cart = $this->getCart();

        $cart->add(new BuyableProduct(1, 'Item', 10.00), 2);

        $cart->setGlobalTax(0);

        $cartItem = $cart->get('027c91341fd5cf4d2579b49c4b6a90da');

        $this->assertEquals('20.00', $cartItem->total(2));
    }

    /** @test */
    public function can_change_discount_globally()
    {
        $cart = $this->getCart();

        $cart->add(new BuyableProduct(1, 'Item', 10.00), 2);

        $cart->setGlobalTax(0);
        $cart->setGlobalDiscount(50);

        $cartItem = $cart->get('027c91341fd5cf4d2579b49c4b6a90da');

        $this->assertEquals('10.00', $cartItem->total(2));
    }

    /** @test */
    public function cart_hast_no_rounding_errors()
    {
        $cart = $this->getCart();

        $cart->add(new BuyableProduct(1, 'Item', 10.004), 2);

        $cartItem = $cart->get('027c91341fd5cf4d2579b49c4b6a90da');

        $this->assertEquals('24.21', $cartItem->total(2));
    }

    /** @test */
    public function it_can_merge_multiple_carts()
    {
        $this->artisan('migrate', [
            '--database' => 'testing',
        ]);

        Event::fake();

        $cart = $this->getCartDiscount(50);
        $cart->add(new BuyableProduct(1, 'Item', 10.00), 1);
        $cart->add(new BuyableProduct(2, 'Item 2', 10.00), 1);
        $cart->store('test');

        $cart2 = $this->getCart();
        $cart2->instance('test2');
        $cart2->setGlobalTax(0);
        $cart2->setGlobalDiscount(0);

        $this->assertEquals('0', $cart2->countInstances());

        $cart2->merge('test');

        $this->assertEquals('2', $cart2->countInstances());
        $this->assertEquals(20, $cart2->totalFloat());

        $cart3 = $this->getCart();
        $cart3->instance('test3');
        $cart3->setGlobalTax(0);
        $cart3->setGlobalDiscount(0);

        $cart3->merge('test', true);

        $this->assertEquals(10, $cart3->totalFloat());
    }

    /** @test */
    public function it_cant_merge_non_existing_cart()
    {
        $this->artisan('migrate', [
            '--database' => 'testing',
        ]);
        Event::fake();
        $cart = $this->getCartDiscount(50);
        $cart->add(new BuyableProduct(1, 'Item', 10.00), 1);
        $cart->add(new BuyableProduct(2, 'Item 2', 10.00), 1);
        $this->assertEquals(false, $cart->merge('doesNotExist'));
        $this->assertEquals(2, $cart->countInstances());
    }

    /** @test */
    public function cart_can_calculate_all_values()
    {
        $cart = $this->getCartDiscount(50);
        $cart->add(new BuyableProduct(1, 'First item', 10.00), 1);
        $cartItem = $cart->get('027c91341fd5cf4d2579b49c4b6a90da');
        $cart->setTax('027c91341fd5cf4d2579b49c4b6a90da', 19);
        $this->assertEquals('10.00', $cart->initial(2));
        $this->assertEquals(10.00, $cart->initialFloat());
        $this->assertEquals('5.00', $cart->discount(2));
        $this->assertEquals(5.00, $cart->discountFloat());
        $this->assertEquals('5.00', $cart->subtotal(2));
        $this->assertEquals(5.00, $cart->subtotalFloat());
        $this->assertEquals('0.95', $cart->tax(2));
        $this->assertEquals(0.95, $cart->taxFloat());
        $this->assertEquals('5.95', $cart->total(2));
        $this->assertEquals(5.95, $cart->totalFloat());
    }

    /** @test */
    public function can_access_cart_item_propertys()
    {
        $cart = $this->getCartDiscount(50);
        $cart->add(new BuyableProduct(1, 'First item', 10.00), 1);
        $cartItem = $cart->get('027c91341fd5cf4d2579b49c4b6a90da');
        $this->assertEquals(50, $cartItem->discountRate);
    }

    /** @test */
    public function cant_access_non_existant_propertys()
    {
        $cart = $this->getCartDiscount(50);
        $cart->add(new BuyableProduct(1, 'First item', 10.00), 1);
        $cartItem = $cart->get('027c91341fd5cf4d2579b49c4b6a90da');
        $this->assertEquals(null, $cartItem->doesNotExist);
        $this->assertEquals(null, $cart->doesNotExist);
    }

    /** @test */
    public function can_set_cart_item_discount()
    {
        $cart = $this->getCart();
        $cart->add(new BuyableProduct(1, 'First item', 10.00), 1);
        $cartItem = $cart->get('027c91341fd5cf4d2579b49c4b6a90da');
        $cart->setDiscount('027c91341fd5cf4d2579b49c4b6a90da', 50);
        $this->assertEquals(50, $cartItem->discountRate);
    }

    /** @test */
    public function can_set_cart_item_weight_and_calculate_total_weight()
    {
        $cart = $this->getCart();
        $cart->add(new BuyableProduct(1, 'First item', 10.00, 250), 2);
        $cartItem = $cart->get('027c91341fd5cf4d2579b49c4b6a90da');
        $cart->setDiscount('027c91341fd5cf4d2579b49c4b6a90da', 50);
        $this->assertEquals('500.00', $cart->weight(2));
        $this->assertEquals(500.00, $cart->weightFloat());
        $this->assertEquals(500.00, $cartItem->weightTotal);
        $this->assertEquals('250.00', $cartItem->weight(2));
    }

    /** @test */
    public function cart_can_create_and_restore_from_instance_identifier()
    {
        $this->artisan('migrate', [
            '--database' => 'testing',
        ]);

        Event::fake();

        $identifier = new Identifiable('User1', 0);
        $cart = $this->getCart();

        $cart->instance($identifier);
        $this->assertEquals('User1', $cart->currentInstance());

        $cart->add(new BuyableProduct(1, 'First item', 10.00, 250), 2);
        $this->assertItemsInCart(2, $cart);

        $cart->store($identifier);
        $cart->destroy();
        $this->assertItemsInCart(0, $cart);

        $cart->restore($identifier);
        $this->assertItemsInCart(2, $cart);
    }

    /** @test */
    public function cart_can_create_items_from_models_using_the_canbebought_trait()
    {
        $cart = $this->getCartDiscount(50);

        $cart->add(new BuyableProductTrait(1, 'First item', 10.00), 2);

        $cartItem = $cart->get('027c91341fd5cf4d2579b49c4b6a90da');

        $cart->setTax('027c91341fd5cf4d2579b49c4b6a90da', 19);

        $this->assertEquals(10.00, $cartItem->price(2));
        $this->assertEquals(5.00, $cartItem->discount(2));
        $this->assertEquals(10.00, $cartItem->discountTotal(2));
        $this->assertEquals(5.00, $cartItem->priceTarget(2));
        $this->assertEquals(10.00, $cartItem->subtotal(2));
        $this->assertEquals(0.95, $cartItem->tax(2));
        $this->assertEquals(1.90, $cartItem->taxTotal(2));
        $this->assertEquals(5.95, $cartItem->priceTax(2));
        $this->assertEquals(11.90, $cartItem->total(2));
    }

    /** @test */
    public function it_does_calculate_correct_results_with_rational_qtys()
    {
        // https://github.com/Crinsane/LaravelShoppingcart/issues/544
        $cart = $this->getCart();

        $cart->add(new BuyableProductTrait(1, 'First item', 10.00), 0.5);

        $cartItem = $cart->get('027c91341fd5cf4d2579b49c4b6a90da');

        $cart->setGlobalTax(50);

        $this->assertEquals(10.00, $cartItem->price(2));
        $this->assertEquals(5.00, $cart->subtotal(2)); //0.5 qty
        $this->assertEquals(7.50, $cart->total(2)); // plus tax
        $this->assertEquals(2.50, $cart->tax(2)); // tax of 5 Bucks
    }

    /** @test */
    public function it_does_allow_adding_cart_items_with_weight_and_options()
    {
        // https://github.com/bumbummen99/LaravelShoppingcart/pull/5
        $cart = $this->getCart();

        $cartItem = $cart->add('293ad', 'Product 1', 1, 9.99, 550, ['size' => 'large']);

        $this->assertEquals(550, $cartItem->weight);
        $this->assertTrue($cartItem->options->has('size'));
        $this->assertEquals('large', $cartItem->options->size);
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

        return new Cart($session, $events);
    }

    /**
     * Get an instance of the cart with discount.
     *
     * @return \Gloudemans\Shoppingcart\Cart
     */
    private function getCartDiscount($discount = 0)
    {
        $cart = $this->getCart();
        $cart->setGlobalDiscount(50);

        return $cart;
    }

    /**
     * Set the config number format.
     *
     * @param int    $decimals
     * @param string $decimalPoint
     * @param string $thousandSeperator
     */
    private function setConfigFormat($decimals, $decimalPoint, $thousandSeperator)
    {
        $this->app['config']->set('cart.format.decimals', $decimals);
        $this->app['config']->set('cart.format.decimal_point', $decimalPoint);
        $this->app['config']->set('cart.format.thousand_separator', $thousandSeperator);
    }
}
