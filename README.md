## LaravelShoppingcart
[![Build Status](https://travis-ci.org/Crinsane/LaravelShoppingcart.png?branch=master)](https://travis-ci.org/Crinsane/LaravelShoppingcart)
[![Total Downloads](https://poser.pugx.org/gloudemans/shoppingcart/downloads.png)](https://packagist.org/packages/gloudemans/shoppingcart)

A simple shoppingcart implementation for Laravel.

# Be careful - Documentation out-of-date
## The documentation is not up-to-date with version 2. Will be updated soon!

## Installation

Install the package through [Composer](http://getcomposer.org/). 

Run the Composer require command from the Terminal:

    composer require gloudemans/shoppingcart

Now all you have to do is add the service provider of the package and alias the package. To do this open your `config/app.php` file.

Add a new line to the `service providers` array:

	\Gloudemans\Shoppingcart\ShoppingcartServiceProvider::class

And optionally add a new line to the `aliases` array:

	'Cart'            => \Gloudemans\Shoppingcart\Facades\Cart::class,

Now you're ready to start using the shoppingcart in your application.

## Overview
Look at one of the following topics to learn more about LaravelShoppingcart

* [Usage](#usage)
* [Collections](#collections)
* [Instances](#instances)
* [Models](#models)
* [Exceptions](#exceptions)
* [Events](#events)
* [Example](#example)

## Usage

The shoppingcart gives you the following methods to use:

**Cart::add()**

```php
// Basic form
Cart::add('293ad', 'Product 1', 1, 9.99, ['size' => 'large']);

// Array form
Cart::add(['id' => '293ad', 'name' => 'Product 1', 'qty' => 1, 'price' => 9.99, 'options' => ['size' => 'large']]);

// Batch method
Cart::add([
  ['id' => '293ad', 'name' => 'Product 1', 'qty' => 1, 'price' => 10.00],
  ['id' => '4832k', 'name' => 'Product 2', 'qty' => 1, 'price' => 10.00, 'options' => ['size' => 'large']]
]);

 // NEW!!!
 // Have a model implement the Buyable interface
 Cart::add($product, 1, $options);
 
 // Batch add Buyables
 Cart::add([$product1, $product2]);

```

The `add()` method will return the instance of a `CartItem` that was just added to the cart. When you batch insert it will return an array of `CartItem`.

**Cart::update()**

```php
 $rowId = 'da39a3ee5e6b4b0d3255bfef95601890afd80709';

Cart::update($rowId, 2); // Will update the quantity

OR

Cart::update($rowId, ['name' => 'Product 1']); // Will update the name

// NEW!!!
// Update the cart using a Buyable
Cart::update($rowId, $product);

```

**Cart::remove()**

```php
 $rowId = 'da39a3ee5e6b4b0d3255bfef95601890afd80709';

Cart::remove($rowId);
```

**Cart::get()**

```php
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

Cart::total;

Cart::total($decimals, $decimalSeperator, $thousandSeperator);

```

**Cart::tax()**

```php

Cart::tax; // Will return the tax for all items in the cart

Cart::tax($decimals, $decimalSeperator, $thousandSeperator);

```

**Cart::subtotal()**

```php

Cart::subtotal; // Will return the total - tax

Cart::subtotal($decimals, $decimalSeperator, $thousandSeperator);

```


**Cart::count()**

```php

 Cart::count(); // Total items
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

## Models
A new feature is associating a model with the items in the cart. Let's say you have a `Product` model in your application. With the new `associate()` method, you can tell the cart that an item in the cart, is associated to the `Product` model. 

That way you can access your model right from the `CartRowCollection`!

Here is an example:

```php
<?php 

/**
 * Let say we have a Product model that has a name and description.
 */

Cart::associate('Product')->add('293ad', 'Product 1', 1, 9.99, array('size' => 'large'));


$content = Cart::content();


foreach($content as $row)
{
	echo 'You have ' . $row->qty . ' items of ' . $row->product->name . ' with description: "' . $row->product->description . '" in your cart.';
}
```

The key to access the model is the same as the model name you associated (lowercase).
The `associate()` method has a second optional parameter for specifying the model namespace.

## Exceptions
The Cart package will throw exceptions if something goes wrong. This way it's easier to debug your code using the Cart package or to handle the error based on the type of exceptions. The Cart packages can throw the following exceptions:

| Exception                             | Reason                                                                           |
| ------------------------------------- | --------------------------------------------------------------------------------- |
| *CartAlreadyStoredException*       | When trying to store a cart that was already stored using the specified identifier                                                                         |
| *InvalidRowIDException*   | When the `$rowId` that got passed doesn't exists in the current cart             |
| *UnknownModelException*   | When an unknown model is associated to a cart row                                |

## Events

The cart also has events build in. There are five events available for you to listen for.

| Event                | Fired                                   |
| -------------------- | --------------------------------------- |
| cart.add($cartItem)      | When an item is added             |
| cart.update($cartItem)  | When an item in the cart is updated     |
| cart.remove($cartItem)  | When an item is removed from the cart   |
| cart.destroy(cartItem)       | When the cart is destroyed              |

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
