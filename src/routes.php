<?php

// Route::resource('orders', 'OrderController');
Route::resource('order-payments', 'OrderPaymentController');
Route::any('order-payments/{payment_id}/callback', 'OrderPaymentController@callback')->name('order-payments.callback');