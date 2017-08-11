<?php

namespace Goodwong\LaravelShop\Contracts;

use Illuminate\Http\Request;
use Goodwong\LaravelShop\Entities\Order;

interface GatewayInterface
{
    /**
     * get payment id
     * @return string
     */
    public function getTransactionId();

    /**
     * get payment status
     * @return string
     */
    public function getTransactionStatus();

    /**
     * get payment data
     * @return array
     */
    public function getTransactionData();

    /**
     * called on charge
     * 
     * @param  \Goodwong\LaravelShop\Entities\Order  $order
     * @param  string  $brief
     * @return void
     */
    public function onCharge(Order $order, $brief);

    /**
     * called on callback
     * 
     * @param  Illuminate\Http\Request  $request
     * @return void
     */
    public function onCallback(Request $request);
}