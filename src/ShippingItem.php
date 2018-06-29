<?php

namespace Gloudemans\Shoppingcart;


use Gloudemans\Shoppingcart\Contracts\Shippable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

class ShippingItem implements Arrayable, Jsonable
{
    /**
     * The rowID of the shipping item.
     *
     * @var string
     */
    public $rowId;
    /**
     * The ID of the shipping item.
     *
     * @var int|string
     */
    public $id;
    /**
     * The name of the shipping item.
     *
     * @var string
     */
    public $name;
    /**
     * The price without TAX of the shipping item.
     *
     * @var float
     */
    public $price;
    /**
     * The qty for the shipping item.
     * default is 1. Normally only one Shipping charge
     *
     * @var float
     */
    public $qty;
    /**
     * The FQN of the associated model.
     *
     * @var string|null
     */
    private $associatedModel = null;
    /**
     * The tax rate for the shipping item.
     *
     * @var int|float
     */
    private $taxRate = 0;
    /**
     * Defines if this Item Should be Free Shipping
     *
     * @var boolean
     */
    private $isFreeShipping = false;
    /**
     * Defines the discount value off the shippign Item is Applicable
     *
     * @var boolean
     */
    private $shippingDiscount;

    /**
     * CartItem constructor.
     *
     * @param int|string $id
     * @param string $name
     * @param float $price
     */
    public function __construct($id, $name, $price)
    {
        if (empty($id)) {
            throw new \InvalidArgumentException('Please supply a valid identifier.');
        }
        if (empty($name)) {
            throw new \InvalidArgumentException('Please supply a valid name.');
        }
        if (strlen($price) < 0 || !is_numeric($price)) {
            throw new \InvalidArgumentException('Please supply a valid price.');
        }
        $this->id = $id;
        $this->name = $name;
        $this->qty = 1;
        $this->rowId = $this->generateRowId($id, $name, $price);
        $this->isFreeShipping = false;
        $this->shippingDiscount = 0;
        $this->price = floatval($price);
    }

    /**
     * Returns the formatted price without TAX.
     *
     * @param int $decimals
     * @param string $decimalPoint
     * @param string $thousandSeperator
     * @param bool $showCurrency
     * @return string
     */
    public function price($decimals = null, $decimalPoint = null, $thousandSeperator = null, $showCurrency = true)
    {
        if ($this->shippingDiscount && !$this->isFreeShipping) {
            $this->price = $this->price - $this->shippingDiscount;
        } elseif ($this->isFreeShipping) {
            $this->price = 0.00;
        }
        return $this->numberFormat($this->price, $decimals, $decimalPoint, $thousandSeperator, null, $showCurrency);
    }

    /**
     * Returns the formatted price with TAX.
     *
     * @param int $decimals
     * @param string $decimalPoint
     * @param string $thousandSeperator
     * @param bool $showCurrency
     * @return string
     */
    public function priceTax($decimals = null, $decimalPoint = null, $thousandSeperator = null, $showCurrency = true)
    {
        return $this->numberFormat($this->priceTax, $decimals, $decimalPoint, $thousandSeperator, null, $showCurrency);
    }

    /**
     * Returns the formatted subtotal.
     * Subtotal is price for whole CartItem without TAX
     *
     * @param int $decimals
     * @param string $decimalPoint
     * @param string $thousandSeperator
     * @param bool $showCurrency
     * @return string
     */
    public function subtotal($decimals = null, $decimalPoint = null, $thousandSeperator = null, $showCurrency = true)
    {
        return $this->numberFormat($this->subtotal, $decimals, $decimalPoint, $thousandSeperator, null, $showCurrency);
    }

    /**
     * Returns the formatted total.
     * Total is price for whole ShippingItem with TAX
     *
     * @param int $decimals
     * @param string $decimalPoint
     * @param string $thousandSeperator
     * @param bool $showCurrency
     * @return string
     */
    public function total($decimals = null, $decimalPoint = null, $thousandSeperator = null, $showCurrency = true)
    {
        return $this->numberFormat($this->total, $decimals, $decimalPoint, $thousandSeperator, null, $showCurrency);
    }

    /**
     * Returns the formatted tax.
     *
     * @param int $decimals
     * @param string $decimalPoint
     * @param string $thousandSeperator
     * @param bool $showCurrency
     * @return string
     */
    public function tax($decimals = null, $decimalPoint = null, $thousandSeperator = null, $showCurrency = true)
    {
        return $this->numberFormat($this->tax, $decimals, $decimalPoint, $thousandSeperator, null, $showCurrency);
    }

    /**
     * Returns the formatted tax.
     *
     * @param int $decimals
     * @param string $decimalPoint
     * @param string $thousandSeperator
     * @return string
     */
    public function taxTotal($decimals = null, $decimalPoint = null, $thousandSeperator = null)
    {
        return $this->numberFormat($this->taxTotal, $decimals, $decimalPoint, $thousandSeperator);
    }

    /**
     * Set the quantity for this cart item.
     *
     * @param int|float $qty
     */
    public function setQuantity($qty)
    {
        if (empty($qty) || !is_numeric($qty)) {
            throw new \InvalidArgumentException('Please supply a valid quantity.');
        }
        $this->qty = $qty;
    }

    /**
     * Update the cart item from a Shippable.
     *
     * @param Shippable $item
     * @return void
     */
    public function updateFromShippable(Shippable $item)
    {
        $this->id = $item->getShippableIdentifier($this->options);
        $this->name = $item->getShippableDescription($this->options);
        $this->price = $item->getShippablePrice($this->options);
        $this->priceTax = $this->price + $this->tax;
    }

    /**
     * Update the cart item from an array.
     *
     * @param array $attributes
     * @return void
     */
    public function updateFromArray(array $attributes)
    {
        $this->id = array_get($attributes, 'id', $this->id);
        $this->qty = array_get($attributes, 'qty', $this->qty);
        $this->name = array_get($attributes, 'name', $this->name);
        $this->price = array_get($attributes, 'price', $this->price);
        $this->priceTax = $this->price + $this->tax;
        $this->rowId = $this->generateRowId($this->id, $this->options->all());
    }

    /**
     * Associate the shipping item with the given model.
     *
     * @param mixed $model
     * @return ShippingItem
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
     * @return ShippingItem
     */
    public function setTaxRate($taxRate)
    {
        $this->taxRate = $taxRate;

        return $this;
    }

    /**
     * Get an attribute from the shipping item or get the associated model.
     *
     * @param string $attribute
     * @return mixed
     */
    public function __get($attribute)
    {
        if (property_exists($this, $attribute)) {
            return $this->{$attribute};
        }
        if ($attribute === 'priceTax') {
            return $this->price + $this->tax;
        }

        if ($attribute === 'subtotal') {
            return $this->qty * $this->price;
        }

        if ($attribute === 'total') {
            return $this->qty * ($this->priceTax);
        }
        if ($attribute === 'tax') {
            return $this->price * ($this->taxRate / 100);
        }

        if ($attribute === 'taxTotal') {
            return $this->tax * $this->qty;
        }
        if ($attribute === 'model' && isset($this->associatedModel)) {
            return with(new $this->associatedModel)->find($this->id);
        }
        return null;
    }

    /**
     * Create a new instance from a Shippable.
     *
     * @param Shippable $item
     * @return ShippingItem
     */
    public static function fromShippable(Shippable $item)
    {
        return new self($item->getShippableIdentifier(), $item->getShippableDescription(), $item->getShippablePrice());
    }

    /**
     * Create a new instance from the given array.
     *
     * @param array $attributes
     * @return ShippingItem
     */
    public static function fromArray(array $attributes)
    {
        return new self($attributes['id'], $attributes['name'], $attributes['price']);
    }

    /**
     * Create a new instance from the given attributes.
     *
     * @param int|string $id
     * @param string $name
     * @param float $price
     * @param array $options
     * @return ShippingItem
     */
    public static function fromAttributes($id, $name, $price)
    {
        return new self($id, $name, $price);
    }

    /**
     * Generate a unique id for the cart item.
     *
     * @param string $id
     * @param array $options
     * @return string
     */
    protected function generateRowId($id, $name, $price)
    {
        return md5($id . $name . $price);
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'rowId'            => $this->rowId,
            'id'               => $this->id,
            'name'             => $this->name,
            'qty'              => $this->qty,
            'price'            => $this->price,
            'tax'              => $this->tax,
            'subtotal'         => $this->subtotal,
            'freeShipping'     => $this->isFreeShipping,
            'shippingDiscount' => $this->shippingDiscount,
        ];
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param int $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Get the formatted number.
     *
     * @param float $value
     * @param int $decimals
     * @param string $decimalPoint
     * @param string $thousandSeperator
     * @return string
     */
    private function numberFormat(
        $value,
        $decimals,
        $decimalPoint,
        $thousandSeperator,
        $currency = null,
        $showCurrency = true
    ) {
        if (is_null($decimals)) {
            $decimals = is_null(config('cart.format.decimals')) ? 2 : config('cart.format.decimals');
        }
        if (is_null($decimalPoint)) {
            $decimalPoint = is_null(config('cart.format.decimal_point')) ? '.' : config('cart.format.decimal_point');
        }
        if (is_null($thousandSeperator)) {
            $thousandSeperator = is_null(config('cart.format.thousand_seperator')) ? ',' : config('cart.format.thousand_seperator');
        }
        if (is_null($currency)) {
            $currency = is_null(config('cart.format.currency')) ? '' : config('cart.format.currency');
        }
        if (!$showCurrency) {
            $currency = null;
        }
        return $currency . number_format($value, $decimals, $decimalPoint, $thousandSeperator);
    }
}