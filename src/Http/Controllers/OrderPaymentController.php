<?php

namespace Goodwong\Shop\Http\Controllers;

use App\Http\Controllers\Controller;
use Goodwong\Shop\Entities\OrderPayment;
use Goodwong\Shop\Shopping;
use Illuminate\Http\Request;

class OrderPaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Goodwong\Shop\Entities\OrderPayment  $orderPayment
     * @return \Illuminate\Http\Response
     */
    public function show(OrderPayment $orderPayment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Goodwong\Shop\Entities\OrderPayment  $orderPayment
     * @return \Illuminate\Http\Response
     */
    public function edit(OrderPayment $orderPayment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Goodwong\Shop\Entities\OrderPayment  $orderPayment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, OrderPayment $orderPayment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Goodwong\Shop\Entities\OrderPayment  $orderPayment
     * @return \Illuminate\Http\Response
     */
    public function destroy(OrderPayment $orderPayment)
    {
        //
    }

    /**
     * callback from gateway
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  integer  $payment_id
     * @return \Illuminate\Http\Response
     */
    public function callback(Request $request, $payment_id)
    {
        $payment = OrderPayment::findOrFail($payment_id);
        return (new Shopping)->load($payment->order_id)->callback($request, $payment_id);
    }
}
