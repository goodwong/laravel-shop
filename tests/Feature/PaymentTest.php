<?php

namespace Tests\Feature;

use Event;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Goodwong\LaravelShop\Entities\OrderPayment;
use Goodwong\LaravelShop\Events\OrderPaid;

class PaymentTest extends TestCase
{
    /**
     * payment
     * 
     * @var  OrderPayment
     */
    public $payment = null;

    /**
     * setup
     */
    public function setUp()
    {
        parent::setUp();

        $this->payment = OrderPayment::where('gateway', 'wxpay_native')->orderBy('id', 'desc')->first();
        $this->api = route('order-payments.callback', ['payment_id' => $this->payment->id]);
    }
    /**
     * test payment callback.
     *
     * @return void
     */
    public function testCallback()
    {
        $params = [
            'appid' => 'wx2421b1c4370ec43b',
            'attach' => 'test',
            'bank_type' => 'CTF',
            'fee_type' => 'CNY',
            'is_subscribe' => 'Y',
            'mch_id' => '10000100',
            'nonce_str' => '5d2b6c2a8db53831f7eda20af46e531c',
            'openid' => 'oUpF8uMEb4qRXf22hE3X68TekukE',
            'out_trade_no' => $this->payment->transaction_id,
            'result_code' => 'SUCCESS',
            'return_code' => 'SUCCESS',
            'sub_mch_id' => '10000100',
            'time_end' => '20140903131540',
            'total_fee' => '1',
            'trade_type' => 'JSAPI',
            'transaction_id' => '1004400740201409030005092168',
        ];

        $key = config('wechat.payment.key');
        ksort($params, SORT_STRING);
        $s = collect($params)->filter(function ($v, $k) {
            return $v;
        })
        ->map(function ($v, $k) {
            return $k . '=' . urlencode($v);
        })
        ->reduce(function ($c, $v) {
            return $c ? $c . '&' . $v : $v;
        }, '');
        $s .= '&key=' . $key;
        $sign = strtoupper(md5($s));

        $payload = "<?xml version=\"1.0\" encoding=\"utf-8\"?>
            <xml>
              <appid><![CDATA[{$params['appid']}]]></appid>
              <attach><![CDATA[{$params['attach']}]]></attach>
              <bank_type><![CDATA[{$params['bank_type']}]]></bank_type>
              <fee_type><![CDATA[{$params['fee_type']}]]></fee_type>
              <is_subscribe><![CDATA[{$params['is_subscribe']}]]></is_subscribe>
              <mch_id><![CDATA[{$params['mch_id']}]]></mch_id>
              <nonce_str><![CDATA[{$params['nonce_str']}]]></nonce_str>
              <openid><![CDATA[{$params['openid']}]]></openid>
              <out_trade_no><![CDATA[{$params['out_trade_no']}]]></out_trade_no>
              <result_code><![CDATA[{$params['result_code']}]]></result_code>
              <return_code><![CDATA[{$params['return_code']}]]></return_code>
              <sign><![CDATA[{$sign}]]></sign>
              <sub_mch_id><![CDATA[{$params['sub_mch_id']}]]></sub_mch_id>
              <time_end><![CDATA[{$params['time_end']}]]></time_end>
              <total_fee>{$params['total_fee']}</total_fee>
              <trade_type><![CDATA[{$params['trade_type']}]]></trade_type>
              <transaction_id><![CDATA[{$params['transaction_id']}]]></transaction_id>
            </xml>";

        Event::fake();
        // 这里有个问题，会导致测试实例无法获得 $request->getContent()内容，导致测试失败
        $response = $this->call('POST', $this->api, [], [], [], [], $payload);
        $this->assertContains('SUCCESS', $response->getContent());

        Event::assertDispatched(OrderPaid::class);
    }
}
