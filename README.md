## Installation

Install the package through [Composer](http://getcomposer.org/). Edit your project's `composer.json` file by adding:

    "require": {
		"laravel/framework": "4.0.*",
		"gloudemans/shoppingcart": "dev-master"
	}

Next, run the Composer update command from the Terminal:

    composer update
    
Now all you have to do is add the service provider of the package and alias the package. To do this open your `app/config/app.php` file.

Add a new line to the `service providers` array:

	'\Gloudemans\Shoppingcart\ShoppingcartServiceProvider'
	
And finally add a new line to the `aliases` array:

	'Cart'            => 'Gloudemans\Shoppingcart\Facades\Cart',
	
Now you're ready to start using the shoppingcart in your application.

## Usage

The shoppingcart gives you the following methods to use:

**Cart::add()**

	/**
     * Add a row to the cart
     * 
     * @param string $id      Unique ID of the item
     * @param string $name    Name of the item
     * @param int    $qty     Item qty to add to the cart
     * @param float  $price   Price of one item
     * @param Array  $options Array of additional options, such as 'size' or 'color'
     */
     
     Cart::add('293ad', 'Product 1', 1, 9.99, array('size' => 'large'));

**Cart::update()**

	/**
     * Update the quantity of one row of the cart
     * @param  string  $rowId The rowid of the item you want to update
     * @param  integer $qty   New quantity of the item
     * @return boolean
     */
     $rowId = 'da39a3ee5e6b4b0d3255bfef95601890afd80709';
     
     Cart::update($rowId, 2);

**Cart::remove()**
	
	/**
     * Remove a row from the cart
     * 
     * @param  string  $rowId The rowid of the item
     * @return boolean   
     */
     
     $rowId = 'da39a3ee5e6b4b0d3255bfef95601890afd80709';
     
     Cart::remove($rowId);
     
**Cart::get()**

	/**
     * Get a row of the cart by its ID
     * 
     * @param  string $rowId The ID of the row to fetch
     * @return CartRowCollection
     */
     
     $rowId = 'da39a3ee5e6b4b0d3255bfef95601890afd80709';
     
     Cart::get($rowId);
     
**Cart::content()**

	/**
	 * Get the cart content
	 *
	 * @return CartCollection
	 */
	 
	 Cart::content();
	 
**Cart::destroy()**
	
	/**
	 * Empty the cart
	 *
	 * @return boolean
	 */
	 
	 Cart::destroy();
	 
**Cart::total()**
	
	/**
     * Get the price total
     * 
     * @return float
     */
     
     Cart::total();
     
**Cart::count()**

	/**
     * Get the number of items in the cart
     *
     * @param  boolean $totalItems Get all the items (when false, will return the number of rows)
     * @return int
     */
     
     Cart::count();      // Total items
     Cart::count(false); // Total rows
     
## Collections

As you might have seen, the `Cart::content()` and `Cart::get()` methods both return a Collection, a `CartCollection` and a `CartRowCollection`.

These Collections extends the 'native' Laravel 4 Collection class, so all methods you know from this class can also be used on your shopping cart. With some addition to easily work with your carts content.

## Example

Below is a little example of how to list the cart content in a table:
	
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