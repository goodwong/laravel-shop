<?php

namespace Goodwong\Shop;

use Exception;
use Illuminate\Http\Request;
use Goodwong\Shop\Entities\Order;
use Goodwong\Shop\Entities\OrderItem;
use Goodwong\Shop\Entities\OrderPayment;
use Goodwong\Shop\Events\OrderCreated;
use Goodwong\Shop\Events\OrderPaid;
use Goodwong\Shop\Contracts\GatewayInterface;

class Shopping
{
    /**
     * @property Order $order 订单
     */
    private $order = null;

    /**
     * @property array $items 订单明细
     */
    private $items;

    /**
     * @property array $temp
     */
    private $temp = [];

    /**
    * get order
    * 
    * @return Order
    */
    public function order ()
    {
        if (!$this->order) {
            $this->order = new Order;
        }
        return $this->order;
    }

    /**
     * get items
     * 
     * @return Collection
     */
    public function items ()
    {
        if (!$this->items) {
            $this->items = collect();
        }
        return $this->items;
    }

    /**
     * get payments
     * 
     * @return Collection
     */
    public function payments ()
    {
        return $this->order()->payments;
    }

    /**
     * load order with items
     * 
     * @param  int  $order_id
     * @return self
     */
    public function load (int $order_id)
    {
        $this->order = Order::findOrFail($order_id);
        $this->order->load('items');
        $this->items = $this->order->items;
        return $this;
    }

    /**
     * set type
     * 
     * @param  string  $type
     * @return self
     */
    public function type (string $type)
    {
        $this->temp['type'] = $type;
        return $this;
    }

    /**
     * set group
     * 
     * @param  string  $group
     * @return self
     */
    public function group (string $group)
    {
        $this->temp['group'] = $group;
        return $this;
    }

    /**
     * set shop
     * 
     * @param  string  $shopId
     * @return self
     */
    public function shop (string $shopId)
    {
        $this->temp['shop_id'] = $shopId;
        return $this;
    }

    /**
     * with product
     * 
     * @param  object|array  $product
     * @return self
     */
    public function withProduct ($product)
    {
        $this->temp = array_merge($this->temp, [
            // 除了product_id，其他属性都不允许被product覆盖。因为这些属性特意指定，必定是特别强调的
            'type' => $this->temp['type'] ?? data_get($product, 'type'),
            'shop_id' => $this->temp['shop_id'] ?? data_get($product, 'shop_id'),
            'product_id' => data_get($product, 'id') ?? data_get($this->temp, 'product_id'),
            'sku' => $this->temp['sku'] ?? data_get($product, 'sku'),
            'group' => $this->temp['group'] ?? data_get($product, 'group'),
            'name' => $this->temp['name'] ?? data_get($product, 'name'),
            'price' => $this->temp['price'] ?? data_get($product, 'price'),
            'unit' => $this->temp['unit'] ?? data_get($product, 'unit'),
        ]);
        return $this;
    }

    /**
     * set product id
     * 
     * @param  string  $productId
     * @return self
     */
    public function product (string $productId)
    {
        $this->temp['product_id'] = $productId;
        return $this;
    }

    /**
     * set name
     * 
     * @param  string  $name
     * @return self
     */
    public function name (string $name)
    {
        $this->temp['name'] = $name;
        return $this;
    }

    /**
     * set sku
     * 
     * @param  string  $sku
     * @return self
     */
    public function sku (string $sku)
    {
        $this->temp['sku'] = $sku;
        return $this;
    }

    /**
     * set price
     * 
     * @param  int  $price
     * @return self
     */
    public function price (string $price)
    {
        $this->temp['price'] = $price;
        return $this;
    }

    /**
     * set unit
     * 
     * @param  string  $unit
     * @return self
     */
    public function unit (string $unit)
    {
        $this->temp['unit'] = $unit;
        return $this;
    }

    /**
     * set row total
     * 
     * @param  int  $rowTotal
     * @return self
     */
    public function rowTotal (int $rowTotal)
    {
        $this->temp['row_total'] = $rowTotal;
        return $this;
    }

    /**
     * set specs
     * 
     * @param  array  $specs
     * @return self
     */
    public function specs (array $specs)
    {
        $this->temp['data'] = $specs;
        return $this;
    }

    /**
     * set comment
     * 
     * @param  string  $comment
     * @return self
     */
    public function comment (string $comment)
    {
        $this->temp['comment'] = $comment;
        return $this;
    }

    /**
     * append order item. 
     * 
     * @param  int  $qty
     * @return void
     */
    public function add (int $qty = null)
    {
        if (!isset($this->temp['name'])) {
            throw Exception('name field is required!');
        }
        // compute
        $this->temp['qty'] = $qty;
        if (!isset($this->temp['row_total']) && $this->temp['qty'] && data_get($this->temp, 'price')) {
            $this->temp['row_total'] = $this->temp['price'] * $this->temp['qty'];
        }

        // add
        $orderItem = new OrderItem($this->temp);
        $this->items()->push($orderItem);
        $this->computeOrderAmount();

        // clear
        $this->temp = [];
        // return $orderItem;
    }

    /**
     * set order context
     * 
     * @param  string  $context
     * @return self
     */
    public function context (string $context)
    {
        $this->order()->context = $context;
        return $this;
    }

    /**
     * set order currency
     * 
     * @param  string  $currency
     * @return self
     */
    public function currency (string $currency)
    {
        $this->order()->currency = $currency;
        return $this;
    }

    /**
     * set user id
     * 
     * @param  int  $userId
     * @return self
     */
    public function user (int $userId)
    {
        $this->order()->user_id = $userId;
        return $this;
    }

    /**
     * set agent id
     * 
     * @param  int  $userId
     * @return self
     */
    public function agent (int $userId)
    {
        $this->order()->agent_id = $userId;
        return $this;
    }

    /**
     * set contacts
     * 
     * @param  array  $contacts
     * @return self
     */
    public function contacts (array $contacts)
    {
        $this->order()->contacts = $contacts;
        return $this;
    }

    /**
     * set comment
     * 
     * @param  string  $comment
     * @return self
     */
    public function orderComment (string $comment)
    {
        $this->order()->comment = $comment;
        return $this;
    }

    /**
     * set status
     * 
     * @param  string  $status
     * @return self
     */
    public function status (string $status)
    {
        $this->order()->status = $status;
        return $this;
    }

    /**
     * append record
     * @TODO 以后转移到独立的表里面
     * 
     * @param  string  $content
     * @param  int  $revisor
     * @return self
     */
    public function record (string $content, int $revisor = null)
    {
        $order = $this->order();
        $records = (array)$order->records;
        $records[] = [
            'revisor' => $revisor,
            'content' => $content,
            'timestamp' => date('Y-m-d H:i:s'),
        ];
        $order->records = $records;
        return $this;
    }

    /**
     * save order and it's items
     * 
     * @return self
     */
    public function save ()
    {
        $isNew = !$this->order()->id;
        if ($isNew && !isset($this->order()->user_id) && !isset($this->order()->agent_id)) {
            $this->order()->user_id = request()->user()->id ?? null;
        }

        // save
        $this->computeOrderAmount();
        $this->order()->save();
        $this->items()->each(function ($item) {
            $item->order_id = $this->order()->id;
            $item->save();
        });

        // dispatch event
        if ($isNew) {
            event(new OrderCreated($this->order()));
        }
        return $this;
    }

    /**
     * soft delete order & items & payments
     * 
     * @return void
     */
    public function delete ()
    {
        $this->order()->delete();
        $this->order()->items->each->delete();
        $this->order()->payments->each->delete();
    }

    /**
     * update order amount
     * 
     * @return void
     */
    private function computeOrderAmount ()
    {
        $this->order()->subtotal = $this->items()->reduce(function ($c, $i) {
            return $c + ($i->row_total ?? 0);
        }, 0);
        $this->order()->grand_total = $this->order()->subtotal + (int)$this->order()->shipping_amount + (int)$this->order()->discount_amount;
    }

    /**
     * charge
     * 
     * @param  string  $gatewayCode
     * @param  int  $amount 可选，指定支付金额，默认订单金额
     * @param  array $params 包含支付所需要的其它参数，比如微信支付的商家名称、用户openid等等
     * @return OrderPayment
     */
    public function charge (string $gatewayCode, int $amount = null, array $params = [])
    {
        // check
        $order = $this->order();
        if (!$order->id) {
            throw new Exception('order not saved');
        }

        // saving...
        $order->update(['status' => 'paying']);
        $payment = OrderPayment::create([
            'order_id' => $order->id,
            'amount' => $amount ?? $order->grand_total,
            'gateway' => $gatewayCode,
            'comment' => $params['comment'] ?? '',
            'transaction_id' => $this->getSerialNumber($order),
        ]);

        $params = array_merge($params, [
            'user_id' => $order->user_id,
            'order_id' => $order->id,
            'payment_serial' => $payment->transaction_id,
            'amount' => abs($payment->amount),
        ]);
        try {
            $gateway = $this->getGateway($gatewayCode, $payment->id);
            $gateway->onCharge($params);

            // update...
            $this->updatePaymentFromGateway($payment, $gateway);
            $this->updateOrderAfterPaid($payment);

            // check
            if ($payment->status === 'failure') {
                throw new \Exception(json_encode($payment->data));
            }
        } catch (Exception $e) {
            $payment->update(['status' => 'failure', 'data' => $e->getMessage(),]);
            throw $e;
        }

        return $payment;
    }

    /**
     * get payment serial number
     * 
     * @param  \Goodwong\Shop\Entities\Order  $order
     * @return string
     */
    protected function getSerialNumber(Order $order)
    {
        return 'OD' . date('YmdHis') . str_pad($order->id, 8, '0', STR_PAD_LEFT);
    }

    /**
     * callback
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $payment_id
     * @return Response
     */
    public function callback (Request $request, int $payment_id)
    {
        $payment = OrderPayment::findOrFail($payment_id);

        try {
            $gateway = $this->getGateway($payment->gateway, $payment->id);
            $response = $gateway->onCallback($request);

            // update...
            $this->updatePaymentFromGateway($payment, $gateway);
            $this->updateOrderAfterPaid($payment);
        } catch (Exception $e) {
            // 异常不是业务结果，不允许覆盖掉原有的data
            // $payment->update(['status' => 'failure', 'data' => (array)$e->getMessage(),]);
            abort(500, $e->getMessage());
        }

        return $response;
    }

    /**
     * get gateway
     * 
     * @param  string  $gatewayCode
     * @param  int  $payment_id
     * @return  Gateway
     */
    private function getGateway (string $gatewayCode, int $payment_id)
    {
        $className = config("shop.gateways.{$gatewayCode}");
        if ($className && class_exists($className)) {
            return new $className($payment_id);
        }
        throw new Exception('invalid gateway: ' . $gatewayCode);
    }

    /**
     * update payment from gateway
     * 
     * @param  OrderPayment  $payment
     * @param  GatewayInterface  $gateway
     * @return void
     */
    private function updatePaymentFromGateway (OrderPayment $payment, GatewayInterface $gateway)
    {
        $data = $gateway->result();
        if ($data) {
            $payment->data = $data;
        }
        $transaction_status = $gateway->status();
        if ($transaction_status) {
            $payment->status = $transaction_status;
        }
        if ($payment->status === 'success') {
            $payment->paid_at = date('Y-m-d H:i:s');
        }
        $payment->save();
    }

    /**
     * update order status after paid
     * 
     * @param  OrderPayment  $payment
     * @return void
     */
    private function updateOrderAfterPaid (OrderPayment $payment)
    {
        // check
        if (!$this->order()->id) {
            throw new Exception('order_id empty!');
        }
        if ($payment->status !== 'success') {
            return;
        }

        // update
        $order = $this->order();
        $paid_total = $order->payments->where('status', 'success')->sum('amount');
        $order->paid_total = $paid_total;
        if ($order->status === 'paying' && $order->paid_total >= $order->grand_total) {
            $order->status = 'new';
        }
        $this->record('支付成功！');
        $order->save();

        // dispatch event
        event(new OrderPaid($order, $payment));
    }

    /**
     * refund
     * 
     * @param  int  $amount
     * @param  array  $params { comment }
     * @return OrderPayment
     */
    public function refund ($amount = null, $params = [])
    {
        $amount = $amount ?? $this->order()->paid_total;
        if (!$amount) {
            throw new Exception('no paid amount to refund!');
        }

        // 基本上都是一次行付清的，所以这里只查找最近一次付款记录哈
        $order = $this->order();
        $lastPayment = OrderPayment::orderBy('id', 'desc')
            ->where('status', 'success')
            ->where('order_id', $order->id)
            ->first();

        // refund...
        $refund = OrderPayment::create([
            'order_id' => $order->id,
            'amount' => -($amount ?? $order->paid_total),
            'gateway' => $lastPayment->gateway,
            'comment' => $params['comment'] ?? '',
            'transaction_id' => "{$lastPayment->transaction_id}-" . date('YmdHis'),
        ]);

        $params = array_merge($params, [
            'order_id' => $order->id,
            'payment_id' => $refund->id,
            'payment_serial' => $lastPayment->transaction_id,
            'refund_serial' => $refund->transaction_id,
            'paid_total' => $lastPayment->amount,
            'amount' => abs($refund->amount),
        ]);
        try {
            $gateway = $this->getGateway($lastPayment->gateway, $refund->id);
            $gateway->onRefund($params);

            // update...
            $this->updatePaymentFromGateway($refund, $gateway);
            $this->updateOrderAfterRefund($refund);

            // check
            if ($refund->status === 'failure') {
                throw new \Exception(json_encode($refund->data));
            }
        } catch (Exception $e) {
            $refund->update(['status' => 'failure', 'data' => $e->getMessage(),]);
            throw $e;
        }

        return $refund;
    }

    /**
     * update order status after refund
     * 
     * @param  OrderPayment  $payment
     * @return void
     */
    private function updateOrderAfterRefund (OrderPayment $payment)
    {
        // check
        if (!$this->order()->id) {
            throw new Exception('order_id empty!');
        }
        if ($payment->status !== 'success') {
            return;
        }

        // update
        $order = $this->order();
        $paid_total = $order->payments->where('status', 'success')->sum('amount');
        $order->paid_total = $paid_total;

        $amount = '¥' . number_format($payment->amount / 100, 2);
        $this->record("退款{$amount}成功！");
        $order->save();

        // dispatch event
        // event(new OrderRefund($order, $payment));
    }

    /**
     * to array
     * 
     * @return array
     */
    public function toArray ()
    {
        $order = $this->order()->toArray();
        $order['items'] = $this->items()->toArray();
        return $order;
    }

    /**
     * to string
     * 
     * @return string
     */
    public function __toString ()
    {
        return json_encode($this->toArray());
    }

    /**
     * print
     * 
     * @return string
     */
    public function print ()
    {
        $lineSize = 23;

        $order = $this->order();
        $items = $this->items();

        $lines = [];
        $lines[] = "\n/**********************";
        $lines[] = "【联系信息】";
        $lines[] = implode(' ', [data_get($order, 'contacts.name'), data_get($order, 'contacts.telephone')]);
        $lines[] = data_get($order, 'contacts.address_regions');
        $lines[] = data_get($order, 'contacts.address_detail');
        $lines[] = "\n【产品明细】";
        $items = collect($items);
        $groups = $items->pluck('group')->unique()->values()->all();
        foreach ($groups as $group) {
            if ($group) {
                $lines[] = "--- " . $group . " ---";
            }
            foreach ($items->values()->all() as $item) {
                if (data_get($item, 'group') !== $group) {
                    continue;
                }
                $unit = data_get($item, 'unit', '');
                $part1 = "·" . $item->name . ($item->qty ? " x{$item->qty}{$unit}" : '');
                $part1 = $this->mb_chunk_split($part1, $lineSize);
                $comment = data_get($item, 'comment');
                $comment = $comment ? "({$comment})" : '';
                $comment = $this->mb_chunk_split($comment, $lineSize);
                $rowTotal = $item->row_total ? number_format($item->row_total / 100, 2) . "元" : '';
                if ($comment) {
                    $lines[] = implode("\n", $part1);
                    $rowTotalLen = $lineSize - mb_strwidth($comment[count($comment)-1]);
                    if ($rowTotalLen < mb_strwidth($rowTotal)) {
                        $comment[count($comment)-1] .= "\n";
                        $rowTotalLen = $lineSize;
                    }
                    $rowTotal = str_pad($rowTotal, $rowTotalLen, ' ', STR_PAD_LEFT);
                    $lines[] = implode("\n", $comment) . $rowTotal;
                } else {
                    $rowTotalLen = $lineSize - mb_strwidth($part1[count($part1)-1]);
                    if ($rowTotalLen < mb_strwidth($rowTotal)) {
                        $part1[count($part1)-1] .= "\n";
                        $rowTotalLen = $lineSize;
                    }
                    $rowTotal = str_pad($rowTotal, $rowTotalLen, ' ', STR_PAD_LEFT);
                    $lines[] = implode("\n", $part1) . $rowTotal;
                }
            }
        }
        $lines[] = "\n【费用】";
        // $lines[] = "小计" . number_format(data_get($order, 'subtotal') / 100, 2) . "元";
        $lines[] = "总计" . number_format(data_get($order, 'grand_total') / 100, 2) . "元";
        $lines[] = "**********************/";
        return implode("\n", $lines);
    }

    /** 
     * 分割字符串
     * 
     * @param  string  $str  要分割的字符串  
     * @param  int     $chunkSize  指定的长度
     * @return array
     */  
    function mb_chunk_split (string $string, int $chunkSize) {  
        $chunks = [];
        $chunk = '';
        $strLen = mb_strlen($string);
        while ($strLen) {
            $chunk .= mb_substr($string, 0, 1, "utf-8");
            $string = mb_substr($string, 1, $strLen, "utf-8");
            $strLen = mb_strlen($string);
            if (mb_strwidth($chunk) >= $chunkSize) {
                $chunks[] = $chunk;
                $chunk = '';
            }
        }
        if ($chunk) {
            $chunks[] = $chunk;
        }
        return $chunks;
    }
}
