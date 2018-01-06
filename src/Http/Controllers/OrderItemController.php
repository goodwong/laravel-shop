<?php

namespace Goodwong\LaravelShop\Http\Controllers;

use Goodwong\LaravelShop\Entities\OrderItem;
use Illuminate\Http\Request;

class OrderItemController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = OrderItem::getModel();
        if ($order_ids = $request->input('orders')) {
            $query = $query->whereIn('order_id', explode(',', $order_ids));
        }
        if ($product_id = $request->input('product')) {
            $query = $query->where('product_id', $product_id);
        }
        if ($sku = $request->input('sku')) {
            $query = $query->where('sku', $sku);
        }
        return $query->get();
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
        $orderItem = OrderItem::create($request->all());
        return $orderItem;
    }

    /**
     * Display the specified resource.
     *
     * @param  \Goodwong\LaravelShop\Entities\OrderItem  $orderItem
     * @return \Illuminate\Http\Response
     */
    public function show(OrderItem $orderItem)
    {
        return $orderItem;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \Goodwong\LaravelShop\Entities\OrderItem  $orderItem
     * @return \Illuminate\Http\Response
     */
    public function edit(OrderItem $orderItem)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Goodwong\LaravelShop\Entities\OrderItem  $orderItem
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, OrderItem $orderItem)
    {
        $orderItem->update($request->all());
        return $orderItem;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Goodwong\LaravelShop\Entities\OrderItem  $orderItem
     * @return \Illuminate\Http\Response
     */
    public function destroy(OrderItem $orderItem)
    {
        $orderItem->delete();
        return response('deleted!', 204);
    }
}
