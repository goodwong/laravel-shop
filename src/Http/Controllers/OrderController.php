<?php

namespace Goodwong\Shop\Http\Controllers;

use Goodwong\Shop\Entities\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $per_page = $request->input('per_page', 15);

        $query = Order::getModel();
        if ($user_id = $request->input('user')) {
            $query = $query->where('user_id', $user_id);
        }
        if ($context = $request->input('context')) {
            $query = $query->where('context', $context);
        }
        if ($ids = $request->input('ids')) {
            $query = $query->whereIn('id', explode(',', $ids));
        }
        if ($status = $request->input('status')) {
            $query = $query->where('status', $status);
        }
        return $query->paginate($per_page);
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
        $order = Order::create($request->all());
        return $order;
    }

    /**
     * Display the specified resource.
     *
     * @param  \Goodwong\Shop\Entities\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function show(Order $order)
    {
        return $order;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \Goodwong\Shop\Entities\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function edit(Order $order)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Goodwong\Shop\Entities\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Order $order)
    {
        $order->update($request->all());
        return $order;
    }

    /**
     * batch update
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function batchUpdateStatus(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'status' => 'required',
            'new_status' => 'required',
        ]);

        $ids = $request->input('ids');
        $status = $request->input('status');
        $query = Order::getModel();
        $query = $query->whereIn('id', $ids);
        $query = $query->where('status', $status);

        $new_status = $request->input('new_status');
        $effected_rows = $query->update(['status' => $new_status]);
        return response()->json(['effected_rows' => $effected_rows]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Goodwong\Shop\Entities\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function destroy(Order $order)
    {
        $order->delete();
        return response('deleted!', 204);
    }
}
