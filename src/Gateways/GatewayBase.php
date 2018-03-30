<?php

namespace Goodwong\Shop\Gateways;

use Illuminate\Http\Request;
use Goodwong\Shop\Entities\Order;
use Goodwong\Shop\Contracts\GatewayInterface;

abstract class GatewayBase implements GatewayInterface
{
    /**
     * gateway id
     * @var string
     */
    protected $gateway_id = null;

    /**
     * payment id, gererated by database auto increment primary key
     * @var string
     */
    protected $payment_id = null;

    /**
     * transaction_id, provided by gateway
     * @var string
     */
    protected $transaction_id = null;

    /**
     * transaction_status
     * @var string
     */
    protected $transaction_status = null;

    /**
     * transaction data
     * @var array
     */
    protected $transaction_data = [];

    /**
     * Constructor 
     * 
     * @param  string  $gateway_id
     * @param  integer  $payment_id
     * @return void
     */
    public function __construct($gateway_id, $payment_id)
    {
        $this->gateway_id = $gateway_id;
        $this->payment_id = $payment_id;
    }

    /**
     * set transaction id
     * @param  string
     * @return this
     */
    final protected function setTransactionId($transaction_id)
    {
        $this->transaction_id = $transaction_id;
    }

     /**
     * set transaction status
     * @param  string
     * @return this
     */
    final protected function setTransactionStatus($transaction_status)
    {
        $this->transaction_status = $transaction_status;
    }

    /**
     * set transaction data
     * @param  array
     * @return this
     */
    final protected function setTransactionData($transaction_data)
    {
        $this->transaction_data = $transaction_data;
    }

    /**
     * get transaction id
     * @return string
     */
    final public function getTransactionId()
    {
        return $this->transaction_id;
    }

     /**
     * get transaction status
     * @return string
     */
    final public function getTransactionStatus()
    {
        return $this->transaction_status;
    }

    /**
     * get transaction data
     * @return array
     */
    final public function getTransactionData()
    {
        return $this->transaction_data;
    }

    /**
     * called on charge
     * 
     * @param  \Goodwong\Shop\Entities\Order  $order
     * @param  string  $brief
     * @param  integer  $amount
     * @return void
     */
    abstract public function onCharge(Order $order, $brief, $amount);

    /**
     * called on callback
     * 
     * @param  Illuminate\Http\Request  $request
     * @return Response
     */
    abstract public function onCallback(Request $request);

    /**
     * get payment serial number
     * 
     * @param  \Goodwong\Shop\Entities\Order  $order
     * @return string
     */
    protected function getSerialNumber(Order $order)
    {
        return 'OD' . date('YmdHis') . str_pad($order->id, 8, '0', STR_PAD_LEFT);
    }

    /**
     * get callback url
     * 针对异步消息通知的，使用callback
     * 
     * @return string
     */
    final protected function getCallbackUrl()
    {
        return route(config('shop.payment_callback_route'), [
            'payment_id'     => $this->payment_id,
        ]);
    }

    /**
     * get redirect url
     * 有些当场回调到商家页面的，使用redirect方法
     * 
     * @param  \Goodwong\Shop\Entities\Order  $order
     * @return string
     */
    // final protected function getRedirectUrl(Order $order)
    // {
    //     return route(config('shop.payment_redirect_route'), [
    //         'order_id'     => $order->id,
    //     ]);
    // }
}
