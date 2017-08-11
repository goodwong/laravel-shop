<?php

/**
 * shop config
 */

return [
    'payment_callback_route' => env('SHOP_PAYMENT_CALLBACK_ROUTE', 'order-payments.callback'),
    'gateways' => [
        // 'wxpay_native' => \Goodwong\LaravelShopGatewayWxpay\GatewayWxpayNative::class,
    ],
];
