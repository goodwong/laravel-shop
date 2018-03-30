<?php

namespace Goodwong\Shop\Contracts;

use Illuminate\Http\Request;
use Goodwong\Shop\Entities\Order;

interface GatewayInterface
{
    /**
     * constructor
     * 
     * @param  string  $gateway_id
     * @param  integer  $payment_id
     * @return void
     */
    public function __construct($gateway_id, $payment_id);

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
     * @param  \Goodwong\Shop\Entities\Order  $order
     * @param  string  $brief
     * @param  integer  $amount
     * @return void
     */
    public function onCharge(Order $order, $brief, $amount);

    /**
     * called on callback
     * 
     * @param  Illuminate\Http\Request  $request
     * @return void
     */
    public function onCallback(Request $request);
}
