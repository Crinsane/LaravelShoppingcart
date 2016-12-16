<?php

namespace Gloudemans\Shoppingcart;

use Illuminate\Contracts\Support\Arrayable;
use Gloudemans\Shoppingcart\Contracts\Buyable;

class CartItem implements Arrayable
{
    /**
     * The rowID of the cart item.
     *
     * @var string
     */
    public $rowId;

    /**
     * The ID of the cart item.
     *
     * @var int|string
     */
    public $id;

    /**
     * The quantity for this cart item.
     *
     * @var int|float
     */
    public $qty;

    /**
     * The name of the cart item.
     *
     * @var string
     */
    public $name;

    /**
     * The price without TAX of the cart item.
     *
     * @var float
     */
    public $price;
    

    /**
     * The options for this cart item.
     *
     * @var array
     */
    public $options;

    /**
     * The extra information for this cart item.
     *
     * @var array
     */
    public $extras;

    /**
     * The FQN of the associated model.
     *
     * @var string|null
     */
    private $associatedModel = null;

    /**
     * The tax rate for the cart item.
     *
     * @var int|float
     */
    private $taxRate = 0;

    /**
     * The discount rate for the cart item.
     *
     * @var CartItemDiscount
     */
    private $discountRate;

    /**
     * CartItem constructor.
     *
     * @param int|string $id
     * @param string     $name
     * @param float      $price
     * @param array      $options
     * @param array      $extras
     */
    public function __construct($id, $name, $price, array $options = [], array $extras = [])
    {
        if(empty($id)) {
            throw new \InvalidArgumentException('Please supply a valid identifier.');
        }
        if(empty($name)) {
            throw new \InvalidArgumentException('Please supply a valid name.');
        }
        if(strlen($price) < 0 || ! is_numeric($price)) {
            throw new \InvalidArgumentException('Please supply a valid price.');
        }

        $this->id       = $id;
        $this->name     = $name;
        $this->price    = floatval($price);
        $this->options  = new CartItemOptions($options);
        $this->extras  = new CartItemExtras($extras);
        $this->discountRate  = new CartItemDiscount(0, 'currency');
        $this->rowId = $this->generateRowId($id, $options);
    }

    /**
     * Returns the formatted price without TAX.
     *
     * @param int    $decimals
     * @param string $decimalPoint
     * @param string $thousandSeparator
     * @return string
     */
    public function price($decimals = null, $decimalPoint = null, $thousandSeparator = null)
    {
        return $this->numberFormat($this->price, $decimals, $decimalPoint, $thousandSeparator);
    }
    
    /**
     * Returns the formatted price with TAX.
     *
     * @param int    $decimals
     * @param string $decimalPoint
     * @param string $thousandSeparator
     * @return string
     */
    public function priceTax($decimals = null, $decimalPoint = null, $thousandSeparator = null)
    {
        return $this->numberFormat($this->priceTax, $decimals, $decimalPoint, $thousandSeparator);
    }

    /**
     * Returns the formatted price with discount.
     *
     * @param int    $decimals
     * @param string $decimalPoint
     * @param string $thousandSeparator
     * @return string
     */
    public function priceDiscount($decimals = null, $decimalPoint = null, $thousandSeparator = null)
    {
        return $this->numberFormat($this->priceDiscount, $decimals, $decimalPoint, $thousandSeparator);
    }

    /**
     * Returns the formatted subtotal.
     * Subtotal is price for whole CartItem without TAX
     *
     * @param int    $decimals
     * @param string $decimalPoint
     * @param string $thousandSeparator
     * @return string
     */
    public function subtotal($decimals = null, $decimalPoint = null, $thousandSeparator = null)
    {
        return $this->numberFormat($this->subtotal, $decimals, $decimalPoint, $thousandSeparator);
    }
    
    /**
     * Returns the formatted total.
     * Total is price for whole CartItem with TAX
     *
     * @param int    $decimals
     * @param string $decimalPoint
     * @param string $thousandSeparator
     * @return string
     */
    public function total($decimals = null, $decimalPoint = null, $thousandSeparator = null)
    {
        return $this->numberFormat($this->total, $decimals, $decimalPoint, $thousandSeparator);
    }

    /**
     * Returns the formatted tax.
     *
     * @param int    $decimals
     * @param string $decimalPoint
     * @param string $thousandSeparator
     * @return string
     */
    public function tax($decimals = null, $decimalPoint = null, $thousandSeparator = null)
    {
        return $this->numberFormat($this->tax, $decimals, $decimalPoint, $thousandSeparator);
    }
    
    /**
     * Returns the formatted total tax.
     *
     * @param int    $decimals
     * @param string $decimalPoint
     * @param string $thousandSeparator
     * @return string
     */
    public function taxTotal($decimals = null, $decimalPoint = null, $thousandSeparator = null)
    {
        return $this->numberFormat($this->taxTotal, $decimals, $decimalPoint, $thousandSeparator);
    }

    /**
     * Returns the formatted discount.
     *
     * @param int    $decimals
     * @param string $decimalPoint
     * @param string $thousandSeparator
     * @return string
     */
    public function discount($decimals = null, $decimalPoint = null, $thousandSeparator = null)
    {
        return $this->numberFormat($this->discount, $decimals, $decimalPoint, $thousandSeparator);
    }

    /**
     * Returns the formatted total discount.
     *
     * @param int    $decimals
     * @param string $decimalPoint
     * @param string $thousandSeparator
     * @return string
     */
    public function discountTotal($decimals = null, $decimalPoint = null, $thousandSeparator = null)
    {
        return $this->numberFormat($this->discountTotal, $decimals, $decimalPoint, $thousandSeparator);
    }

    /**
     * Set the quantity for this cart item.
     *
     * @param int|float $qty
     */
    public function setQuantity($qty)
    {
        if(empty($qty) || ! is_numeric($qty))
            throw new \InvalidArgumentException('Please supply a valid quantity.');

        $this->qty = $qty;
    }

    /**
     * Set the extras for this cart item.
     *
     * @param array $extras
     */
    public function setExtras($extras)
    {
        $this->extras = new CartItemExtras($extras);
    }

    /**
     * Update the cart item from a Buyable.
     *
     * @param \Gloudemans\Shoppingcart\Contracts\Buyable $item
     * @return void
     */
    public function updateFromBuyable(Buyable $item)
    {
        $this->id       = $item->getBuyableIdentifier($this->options);
        $this->name     = $item->getBuyableDescription($this->options);
        $this->price    = $item->getBuyablePrice($this->options);
        $this->priceTax = $this->taxedPrice();
    }

    /**
     * Update the cart item from an array.
     *
     * @param array $attributes
     * @return void
     */
    public function updateFromArray(array $attributes)
    {
        $this->id       = array_get($attributes, 'id', $this->id);
        $this->qty      = array_get($attributes, 'qty', $this->qty);
        $this->name     = array_get($attributes, 'name', $this->name);
        $this->price    = array_get($attributes, 'price', $this->price);
        $this->priceTax = $this->taxedPrice();
        $this->options  = new CartItemOptions(array_get($attributes, 'options', $this->options));
        $this->extras  = new CartItemExtras(array_get($attributes, 'extras', $this->extras));

        $this->rowId = $this->generateRowId($this->id, $this->options->all());
    }

    /**
     * Associate the cart item with the given model.
     *
     * @param mixed $model
     * @return \Gloudemans\Shoppingcart\CartItem
     */
    public function associate($model)
    {
        $this->associatedModel = is_string($model) ? $model : get_class($model);
        
        return $this;
    }

    /**
     * Set the tax rate.
     *
     * @param int|float $taxRate
     * @return \Gloudemans\Shoppingcart\CartItem
     */
    public function setTaxRate($taxRate)
    {
        $this->taxRate = $taxRate;
        
        return $this;
    }

    /**
     * Set the discount.
     *
     * @param array $attributes
     * @return \Gloudemans\Shoppingcart\CartItem
     */
    public function setDiscount(array $attributes)
    {
        if (!isset($attributes[0])) {
            throw new \InvalidArgumentException('Please supply a valid discount attributes.');
        }

        $this->discountRate = new CartItemDiscount($attributes[0], isset($attributes[1]) ? $attributes[1] : null, isset($attributes[2]) ? $attributes[2] : null);

        return $this;
    }

    /**
     * Get an attribute from the cart item or get the associated model.
     *
     * @param string $attribute
     * @return mixed
     */
    public function __get($attribute)
    {
        if(property_exists($this, $attribute)) {
            return $this->{$attribute};
        }

        if($attribute === 'subtotal') {
            return $this->qty * $this->price;
        }

        if($attribute === 'total') {
            return $this->qty * ($this->priceTax);
        }

        if($attribute === 'priceTax') {
            return $this->taxedPrice();
        }

        if($attribute === 'tax') {
            return ((config('cart.calculate_taxes_on_discounted_price')) ? $this->priceDiscount : $this->price) * ($this->taxRate / 100);
        }

        if($attribute === 'taxTotal') {
            return $this->tax * $this->qty;
        }

        if($attribute === 'priceDiscount') {
            return $this->discountedPrice();
        }

        if($attribute === 'discount') {
            return $this->discountRate->calculateDiscount($this->price);
        }

        if($attribute === 'discountTotal') {
            return $this->qty * $this->discount;
        }

        if($attribute === 'model') {
            return with(new $this->associatedModel)->find($this->id);
        }

        return null;
    }

    /**
     * Create a new instance from a Buyable.
     *
     * @param \Gloudemans\Shoppingcart\Contracts\Buyable $item
     * @param array                                      $options
     * @return \Gloudemans\Shoppingcart\CartItem
     */
    public static function fromBuyable(Buyable $item, array $options = [])
    {
        return new self($item->getBuyableIdentifier($options), $item->getBuyableDescription($options), $item->getBuyablePrice($options), $options);
    }

    /**
     * Create a new instance from the given array.
     *
     * @param array $attributes
     * @return \Gloudemans\Shoppingcart\CartItem
     */
    public static function fromArray(array $attributes)
    {
        $options = array_get($attributes, 'options', []);
        $extras = array_get($attributes, 'extras', []);

        return new self($attributes['id'], $attributes['name'], $attributes['price'], $options, $extras);
    }

    /**
     * Create a new instance from the given attributes.
     *
     * @param int|string $id
     * @param string     $name
     * @param float      $price
     * @param array      $options
     * @param array      $extras
     * @return \Gloudemans\Shoppingcart\CartItem
     */
    public static function fromAttributes($id, $name, $price, array $options = [], array $extras = [])
    {
        return new self($id, $name, $price, $options, $extras);
    }

    /**
     * Generate a unique id for the cart item.
     *
     * @param string $id
     * @param array  $options
     * @return string
     */
    protected function generateRowId($id, array $options)
    {
        ksort($options);

        return md5($id . serialize($options));
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'rowId'    => $this->rowId,
            'id'       => $this->id,
            'name'     => $this->name,
            'qty'      => $this->qty,
            'price'    => $this->price,
            'options'  => $this->options,
            'extras'  => $this->extras,
            'tax'      => $this->tax,
            'discount' => $this->discountRate,
            'subtotal' => $this->subtotal
        ];
    }

    /**
     * Return the price with discount
     *
     * @return int|float
     */
    protected function discountedPrice()
    {

        $value = ($this->discountRate) ? $this->discountRate->applyDiscount($this->price) : $this->price;

        return $value;
    }

    /**
     * Return the price with tax
     *
     * @return int|float
     */
    protected function taxedPrice()
    {
        return $this->priceDiscount + $this->tax;
    }

    /**
     * Get the Formated number
     *
     * @param $value
     * @param $decimals
     * @param $decimalPoint
     * @param $thousandSeparator
     * @return string
     */
    private function numberFormat($value, $decimals, $decimalPoint, $thousandSeparator)
    {
        if(is_null($decimals)){
            $decimals = is_null(config('cart.format.decimals')) ? 2 : config('cart.format.decimals');
        }
        if(is_null($decimalPoint)){
            $decimalPoint = is_null(config('cart.format.decimal_point')) ? '.' : config('cart.format.decimal_point');
        }
        if(is_null($thousandSeparator)){
            $thousandSeparator = is_null(config('cart.format.thousand_separator')) ? ',' : config('cart.format.thousand_separator');
        }

        return number_format($value, $decimals, $decimalPoint, $thousandSeparator);
    }
}
