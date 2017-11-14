<?php

namespace Goodwong\LaravelShop;

use Illuminate\Support\Facades\Route;

class Router
{
    /**
     * product routes
     * 
     * @return void
     */
    public static function product()
    {
        Route::namespace('Goodwong\LaravelShop\Http\Controllers')->group(function () {
        	Route::resource('products', 'ProductController');
        });
    }

    /**
     * order routes
     * 
     * @return void
     */
    public static function order()
    {
        Route::namespace('Goodwong\LaravelShop\Http\Controllers')->group(function () {
        	Route::resource('orders', 'OrderController');
        });
    }

    /**
     * order payment routes
     * 
     * @return void
     */
    public static function payment()
    {
        Route::namespace('Goodwong\LaravelShop\Http\Controllers')->group(function () {
        	Route::resource('order-payments', 'OrderPaymentController');
        });
    }

    /**
     * order payment callback route
     * 
     * @return void
     */
    public static function paymentCallback()
    {
        Route::namespace('Goodwong\LaravelShop\Http\Controllers')->group(function () {
        	Route::any('order-payments/{payment_id}/callback', 'OrderPaymentController@callback')
        	->name('order-payments.callback');
        });
    }
}