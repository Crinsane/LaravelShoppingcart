<?php

namespace Gloudemans\Shoppingcart;

use Illuminate\Support\Collection;
use App\Model\Cart;
class CartItemOptions extends Collection
{
    /**
     * Get the option by the given key.
     *
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->get($key);
    }
}
