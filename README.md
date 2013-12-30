## Installation

Install the package through [Composer](http://getcomposer.org/). Edit your project's `composer.json` file by adding:

```php
"require": {
	"laravel/framework": "4.1.*",
	"gloudemans/shoppingcart": "dev-master"
}
```

Next, run the Composer update command from the Terminal:

    composer update

Now all you have to do is add the service provider of the package and alias the package. To do this open your `app/config/app.php` file.

Add a new line to the `service providers` array:

	'Gloudemans\Shoppingcart\ShoppingcartServiceProvider'

And finally add a new line to the `aliases` array:

	'Cart'            => 'Gloudemans\Shoppingcart\Facades\Cart',

Now you're ready to start using the shoppingcart in your application.

## Overview
Look at one of the following topics to learn more about LaravelShoppingcart

* [Usage](#usage)
* [Collections](#collections)
* [Instances](#instances)
* [Exceptions](#exceptions)
* [Events](#events)
* [Example](#example)

## Usage

The shoppingcart gives you the following methods to use:

**Cart::add()**

```php
/**
 * Add a row to the cart
 *
 * @param string|Array $id      Unique ID of the item|Item formated as array|Array of items
 * @param string       $name    Name of the item
 * @param int          $qty     Item qty to add to the cart
 * @param float        $price   Price of one item
 * @param Array        $options Array of additional options, such as 'size' or 'color'
 */

// Basic form
Cart::add('293ad', 'Product 1', 1, 9.99, array('size' => 'large'));

// Array form
Cart::add(array('id' => '293ad', 'name' => 'Product 1', 'qty' => 1, 'price' => 9.99, 'options' => array('size' => 'large')));

// Batch method
Cart::add(array(
  array('id' => '293ad', 'name' => 'Product 1', 'qty' => 1, 'price' => 10.00),
  array('id' => '4832k', 'name' => 'Product 2', 'qty' => 1, 'price' => 10.00, 'options' => array('size' => 'large'))
));
```

**Cart::addBatch()**

Don't use this anymore, just pass a multidimensional array to the `Cart::add()` method

```php
/**
 * Add multiple rows to the cart
 *
 * @param Array $items An array of items to add, use array keys corresponding to the 'add' method's parameters
 */

Cart::addBatch(array(
	array('id' => '293ad', 'name' => 'Product 1', 'qty' => 1, 'price' => 10.00),
	array('id' => '4832k', 'name' => 'Product 2', 'qty' => 1, 'price' => 10.00, 'options' => array('size' => 'large'))
));
```

**Cart::update()**

```php
/**
 * Update the quantity of one row of the cart
 *
 * @param  string        $rowId       The rowid of the item you want to update
 * @param  integer|Array $attribute   New quantity of the item|Array of attributes to update
 * @return boolean
 */
 $rowId = 'da39a3ee5e6b4b0d3255bfef95601890afd80709';

Cart::update($rowId, 2);

OR

Cart::update($rowId, array('name' => 'Product 1'));
```

**Cart::remove()**

```php
/**
 * Remove a row from the cart
 *
 * @param  string  $rowId The rowid of the item
 * @return boolean
 */

 $rowId = 'da39a3ee5e6b4b0d3255bfef95601890afd80709';

Cart::remove($rowId);
```

**Cart::get()**

```php
/**
 * Get a row of the cart by its ID
 *
 * @param  string $rowId The ID of the row to fetch
 * @return CartRowCollection
 */

$rowId = 'da39a3ee5e6b4b0d3255bfef95601890afd80709';

Cart::get($rowId);
```

**Cart::content()**

```php
/**
 * Get the cart content
 *
 * @return CartCollection
 */

Cart::content();
```

**Cart::destroy()**

```php
/**
 * Empty the cart
 *
 * @return boolean
 */

Cart::destroy();
```

**Cart::total()**

```php
/**
 * Get the price total
 *
 * @return float
 */

Cart::total();
```

**Cart::count()**

```php
/**
 * Get the number of items in the cart
 *
 * @param  boolean $totalItems Get all the items (when false, will return the number of rows)
 * @return int
 */

 Cart::count();      // Total items
 Cart::count(false); // Total rows
```

**Cart::search()**

```php
/**
 * Search if the cart has a item
 *
 * @param  Array  $search An array with the item ID and optional options
 * @return Array|boolean
 */

 Cart::search(array('id' => 1, 'options' => array('size' => 'L'))); // Returns an array of rowid(s) of found item(s) or false on failure
```

## Collections

As you might have seen, the `Cart::content()` and `Cart::get()` methods both return a Collection, a `CartCollection` and a `CartRowCollection`.

These Collections extends the 'native' Laravel 4 Collection class, so all methods you know from this class can also be used on your shopping cart. With some addition to easily work with your carts content.

## Instances

Now the packages also supports multiple instances of the cart. The way this works is like this:

You can set the current instance of the cart with `Cart::instance('newInstance')`, at that moment, the active instance of the cart is `newInstance`, so when you add, remove or get the content of the cart, you work with the `newInstance` instance of the cart.
If you want to switch instances, you just call `Cart::instance('otherInstance')` again, and you're working with the `otherInstance` again.

So a little example:

```php
Cart::instance('shopping')->add('192ao12', 'Product 1', 1, 9.99);

// Get the content of the 'shopping' cart
Cart::content();

Cart::instance('wishlist')->add('sdjk922', 'Product 2', 1, 19.95, array('size' => 'medium'));

// Get the content of the 'wishlist' cart
Cart::content();

// If you want to get the content of the 'shopping' cart again...
Cart::instance('shopping')->content();

// And the count of the 'wishlist' cart again
Cart::instance('wishlist')->count();
```

N.B. Keep in mind that the cart stays in the last set instance for as long as you don't set a different one during script execution.

N.B.2 The default cart instance is called `main`, so when you're not using instances,`Cart::content();` is the same as `Cart::instance('main')->content()`.

## Exceptions
The Cart package will throw exceptions if something goes wrong. This way it's easier to debug your code using the Cart package or to handle the error based on the type of exceptions. The Cart packages can throw the following exceptions:

| Exception                             | Reason                                                                   |
| ------------------------------------- | ------------------------------------------------------------------------ |
| *ShoppingcartInstanceException*       | When no instance is passed to the instance() method                      |
| *ShoppingcartInvalidItemException*    | When a new product misses one of it's arguments (id, name, qty, price)   |
| *ShoppingcartInvalidPriceException*   | When a not numeric price is passed                                       |
| *ShoppingcartInvalidQtyException*     | When a not numeric quantity is passed                                    |
| *ShoppingcartInvalidRowIDException*   | When the rowId that got passed doesn't exists in the current cart        |

## Events

The cart also has events build in. There are five events available for you to listen for.

| Event                | Fired                                   |
|----------------------------------------------------------------|
| cart.add($item)      | When a single item is added             |
| cart.batch($items)   | When a batch if items is added          |
| cart.update($rowId)  | When an item in the cart is updated     |
| cart.remove($rowId)  | When an item is removed from the cart   |
| cart.destroy()       | When the cart is destroyed              |

## Example

Below is a little example of how to list the cart content in a table:

```php
// Controller

Cart::add('192ao12', 'Product 1', 1, 9.99);
Cart::add('1239ad0', 'Product 2', 2, 5.95, array('size' => 'large'));

// View

<table>
   	<thead>
       	<tr>
           	<th>Product</th>
           	<th>Qty</th>
           	<th>Item Price</th>
           	<th>Subtotal</th>
       	</tr>
   	</thead>

   	<tbody>

   	<?php foreach($cart as $row) :?>

       	<tr>
           	<td>
               	<p><strong><?php echo $row->name;?></strong></p>
               	<p><?php echo ($row->options->has('size') ? $row->options->size : '');?></p>
           	</td>
           	<td><input type="text" value="<?php echo $row->qty;?>"></td>
           	<td>$<?php echo $row->price;?></td>
           	<td>$<?php echo $row->subtotal;?></td>
       </tr>

   	<?php endforeach;?>

   	</tbody>
</table>
```
