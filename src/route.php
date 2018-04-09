<?php

// product
Route::resource('products', 'Goodwong\Shop\Http\Controllers\ProductController');
Route::post('product-images', 'Goodwong\Shop\Http\Controllers\ProductImageController@store');
Route::delete('product-images', 'Goodwong\Shop\Http\Controllers\ProductImageController@destroy');

// order
Route::put('orders/batch-update-status', 'Goodwong\Shop\Http\Controllers\OrderController@batchUpdateStatus');
Route::resource('orders', 'Goodwong\Shop\Http\Controllers\OrderController');
Route::resource('order-items', 'Goodwong\Shop\Http\Controllers\OrderItemController');

// payment
Route::resource('order-payments', 'Goodwong\Shop\Http\Controllers\OrderPaymentController');
Route::any('order-payments/{payment_id}/callback', 'Goodwong\Shop\Http\Controllers\OrderPaymentController@callback')
    ->name('order-payments.callback');
