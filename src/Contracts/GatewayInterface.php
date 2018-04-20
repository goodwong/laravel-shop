<?php

namespace Goodwong\Shop\Contracts;

use Illuminate\Http\Request;

interface GatewayInterface
{
    /**
     * constructor
     * 
     * @param  int  $payment_id
     * @return void
     */
    public function __construct (int $payment_id);

    /**
     * get payment status
     * @return string
     */
    public function status ();

    /**
     * set or get payment data
     * 
     * @param  mixed  $result
     * @return array
     */
    public function result ($result = null);

    /**
     * called on charge
     * 
     * @param  array  $params 包含支付所需要的其它参数，比如微信支付的商家名称、用户openid等等
     * @return void
     */
    public function onCharge (array $params);

    /**
     * called on callback
     * 
     * @param  Illuminate\Http\Request  $request
     * @return void
     */
    public function onCallback (Request $request);

    /**
     * on refund
     * 
     * @param  array  $params
     * @return void
     */
    public function onRefund (array $params);
}
