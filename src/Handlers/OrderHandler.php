<?php

namespace Goodwong\LaravelShop;

use Goodwong\LaravelShop\Entities\Order;

class OrderHandler
{
    /**
    * new order
    * 
    * @return Order
    */
    public function newOrder()
    {
        //
    }

    /**
     * load order with items
     * 
     * @param  integer  $id
     * @return Order
     */
    public function loadOrder()
    {
        //
    }

    /**
     * append order item
     * 
     * @param  Order  $order
     * @param  Product | array  $product
     * @param  integer  $quantity
     * @param  string  $group  optional
     * @return OrderItem
     */
    public function append(Order $order, $product, $quantity = 1, $group = null)
    {
        //
    }

    /**
     * save order and it's items
     * 
     * @return void
     */
    public function save()
    {
        //
    }
}
