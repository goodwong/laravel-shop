<?php

namespace Goodwong\Shop\Http\Controllers;

use Goodwong\Shop\Entities\Order;
use Goodwong\Shop\Entities\Product;
use Goodwong\Shop\Shopping;
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
        $query = $query->with(['items', 'payments']);
        return $query->paginate($per_page);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // order
        // 接受：context / user_id / contacts / comment / expected_at
        $shopping = (new Shopping)
            ->contacts($request->input('contacts'));
        if ($request->input('comment')) {
            $shopping->orderComment($request->input('comment'));
        }
        if ($request->input('user_id')) {
            $shopping->user($request->input('user_id'));
        }
        if ($request->input('context')) {
            $shopping->order()->context = $request->input('context');
        }
        if ($request->input('expected_at')) {
            $shopping->order()->expected_at = $request->input('expected_at');
        }

        // items...
        // 接受 group / product_id / comment / specs / qty
        // 这里根据业务需求，基本上都会重写
        foreach ((array)$request->input('items') as $item) {
            $product = Product::findOrFail($item['product_id']);
            $shopping->withProduct($product);
            if (isset($item['group'])) {
                $shopping->comment($item['group']);
            }
            if (isset($item['comment'])) {
                $shopping->comment($item['comment']);
            }
            if (isset($item['specs'])) {
                $shopping->specs($item['specs']);
                // specs 如果影响价格，则需要在这里计算 row_total
                // $shopping->rowTotal($computedRowTotal);
            }
            $shopping->add($item['qty'] ?? null);
        }

        // coupon...
        // if ($request->input('coupon_id')) {
        //     $coupon = Coupon::findOrFail($request->input('coupon_id'));
        //     $discountAmount = $this->computeDiscount($shopping, $coupon);
        //     $shopping->type('discount')->group('优惠')->name($coupon->name)->rowTotal($discountAmount)->add();
        // }
        $shopping->save();
        return $shopping;
    }

    /**
     * Display the specified resource.
     *
     * @param  \Goodwong\Shop\Entities\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function show(Order $order)
    {
        return $order->load(['items', 'payments']);
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
        return $order->load(['items', 'payments']);
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
