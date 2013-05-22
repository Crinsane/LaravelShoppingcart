<?php namespace Gloudemans\Shoppingcart;

use Illuminate\Support\Collection;

class CartRowOptionsCollection extends Collection {

    public function __construct($items)
    {
        parent::__construct($items);
    }

    public function __get($arg)
    {
        if($this->has($arg))
        {
            return $this->get($arg);
        }

        return NULL;
    }

}