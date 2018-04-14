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
     * @param  int  $payment_id
     * @return void
     */
    public function __construct(string $gateway_id, int $payment_id);

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
     * @param  int  $amount
     * @param  array  $params 包含支付所需要的其它参数，比如微信支付的商家名称、用户openid等等
     * @return void
     */
    public function onCharge(Order $order, int $amount, array $params = []);

    /**
     * called on callback
     * 
     * @param  Illuminate\Http\Request  $request
     * @return void
     */
    public function onCallback(Request $request);
}
