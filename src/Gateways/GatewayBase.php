<?php

namespace Goodwong\Shop\Gateways;

use Illuminate\Http\Request;
use Goodwong\Shop\Contracts\GatewayInterface;

abstract class GatewayBase implements GatewayInterface
{
    /**
     * payment id, gererated by database auto increment primary key
     * @var string
     */
    protected $payment_id = null;

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
     * @param  int  $payment_id
     * @return void
     */
    public function __construct (int $payment_id)
    {
        $this->payment_id = $payment_id;
    }

    /**
     * pendding
     * @return void
     */
    final protected function pendding ()
    {
        $this->transaction_status = 'pendding';
    }

    /**
     * success
     * @return void
     */
    final protected function success ()
    {
        $this->transaction_status = 'success';
    }

    /**
     * failure
     * @return void
     */
    final protected function failure ()
    {
        $this->transaction_status = 'failure';
    }

    /**
     * set transaction data
     * @param  mixed  $data
     * @return array|void
     */
    final public function result ($data = null)
    {
        if ($data) {
            $this->transaction_data = $data;
        } else {
            return $this->transaction_data;
        }
    }

     /**
     * get transaction status
     * @return string
     */
    final public function status ()
    {
        return $this->transaction_status;
    }

    /**
     * called on charge
     * 
     * @param  array  $params
     * @return void
     */
    abstract public function onCharge (array $params);

    /**
     * called on callback
     * 
     * @param  Illuminate\Http\Request  $request
     * @return Response
     */
    public function onCallback (Request $request)
    {
        return response('nothing...');
    }

    /**
     * on refund
     * 
     * @param  array  $params
     * @return void
     */
    public function onRefund (array $params)
    {
        //
    }

    /**
     * get callback url
     * 针对异步消息通知的，使用callback
     * 
     * @return string
     */
    final protected function callbackUrl ()
    {
        return route(config('shop.payment_callback_route'), [
            'payment_id'     => $this->payment_id,
        ]);
    }
}
