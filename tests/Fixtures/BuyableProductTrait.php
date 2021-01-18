<?php

namespace Gloudemans\Tests\Shoppingcart\Fixtures;

use Gloudemans\Shoppingcart\Contracts\Buyable;
use Illuminate\Database\Eloquent\Model;

class BuyableProductTrait extends Model implements Buyable
{
    use \Gloudemans\Shoppingcart\CanBeBought;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'name',
        'title',
        'description',
        'price',
        'weight',
    ];

    protected $attributes = [
        'id'     => 1,
        'name'   => 'Item name',
        'price'  => 10.00,
        'weight' => 0,
    ];
}
