<?php

namespace Gloudemans\Shoppingcart;


use Gloudemans\Shoppingcart\Contracts\Discountable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

class DiscountItem implements Arrayable, Jsonable
{
    /**
     * The rowID of the discount item.
     *
     * @var string
     */
    public $rowId;
    /**
     * The ID of the discount item.
     *
     * @var int|string
     */
    public $id;
    /**
     * The name of the discount item.
     *
     * @var string
     */
    public $name;
    /**
     * The Value of the discount item
     *
     * @var float
     */
    public $value;
    /**
     * Defines the Number of Discountable Items
     *
     * @var int
     */
    public $qty;
    /**
     * The FQN of the associated model.
     *
     * @var string|null
     */
    private $associatedModel = null;
    /**
     * Defines the Type of Discount,
     * Monetary Vlaue or Percentage
     *
     * @var string
     */
    private $type;

    /**
     * DiscountItem constructor.
     *
     * @param int|string $id
     * @param string $name
     * @param float $value
     */
    public function __construct($id, $name, $value, $type)
    {
        if (empty($id)) {
            throw new \InvalidArgumentException('Please supply a valid identifier.');
        }
        if (empty($name)) {
            throw new \InvalidArgumentException('Please supply a valid name.');
        }
        if (strlen($value) < 0 || !is_numeric($value)) {
            throw new \InvalidArgumentException('Please supply a valid value.');
        }
        if (empty($type) || !in_array($type, ['monetary', 'percent'])) {
            throw new \InvalidArgumentException('Please supply a valid discount type, monetary or percent.');
        }
        $this->id = $id;
        $this->name = $name;
        $this->type = $type;
        $this->value = floatval($value);
        $this->rowId = $this->generateRowId($id, $name, $value, $type);
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
        $this->name = array_get($attributes, 'name', $this->name);
        $this->value = array_get($attributes, 'value', $this->value);
        $this->type = array_get($attributes, 'type', $this->type);
        $this->rowId = $this->generateRowId($this->id, $this->options->all());
    }

    /**
     * Get an attribute from the discount item
     *
     * @param string $attribute
     * @return mixed
     */
    public function __get($attribute)
    {
        if (property_exists($this, $attribute)) {
            return $this->{$attribute};
        }
        if ($attribute === 'model' && isset($this->associatedModel)) {
            return with(new $this->associatedModel)->find($this->id);
        }
        return null;
    }

    /**
     * Create a new instance from a Discountable.
     *
     * @param Discountable $item
     * @param array $options
     * @return DiscountItem
     */
    public static function fromDiscountable(Discountable $item, array $options = [])
    {
        return new self($item->getDiscountableIdentifier($options), $item->getDiscountableDescription($options),
            $item->getDiscountableValue($options), $item->getDiscountableType($options), $options);
    }

    /**
     * Create a new instance from the given array.
     *
     * @param array $attributes
     * @return DiscountItem
     */
    public static function fromArray(array $attributes)
    {
        return new self($attributes['id'], $attributes['name'], $attributes['value'], $attributes['type']);
    }

    /**
     * Create a new instance from the given attributes.
     *
     * @param int|string $id
     * @param string $name
     * @param float $price
     * @param array $options
     * @return \Gloudemans\Shoppingcart\DiscountItem
     */
    public static function fromAttributes($id, $name, $value, $type)
    {
        return new self($id, $name, $value, $type);
    }

    /**
     * Generate a unique id for the cart item.
     *
     * @param string $id
     * @param array $options
     * @return string
     */
    protected function generateRowId($id, $name, $value, $type)
    {
        return md5($id . $name . $value . $type);
    }

    /**
     * Set the quantity for this discount item.
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
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'rowId' => $this->rowId,
            'id'    => $this->id,
            'name'  => $this->name,
            'value' => $this->value,
            'qty'   => $this->qty,
            'type'  => $this->type,
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
     * Associate the discount item with the given model.
     *
     * @param mixed $model
     * @return DiscountItem
     */
    public function associate($model)
    {
        $this->associatedModel = is_string($model) ? $model : get_class($model);

        return $this;
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
    private function numberFormat($value, $decimals, $decimalPoint, $thousandSeperator)
    {
        if (is_null($decimals)) {
            $decimals = is_null(config('cart.format.decimals')) ? 2 : config('cart.format.decimals');
        }
        if (is_null($decimalPoint)) {
            $decimalPoint = is_null(config('cart.format.decimal_point')) ? '.' : config('cart.format.decimal_point');
        }
        if (is_null($thousandSeperator)) {
            $thousandSeperator = is_null(config('cart.format.thousand_seperator')) ? ',' : config('cart.format.thousand_seperator');
        }
        return number_format($value, $decimals, $decimalPoint, $thousandSeperator);
    }
}