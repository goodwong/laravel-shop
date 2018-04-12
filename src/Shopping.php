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
            'type' => $this->temp['type'] ?? data_get($product, 'type'),
            'shop_id' => $this->temp['shop_id'] ?? data_get($product, 'shop_id'),
            'product_id' => data_get($product, 'id'),
            'name' => $this->temp['name'] ?? data_get($product, 'name'),
            'sku' => $this->temp['sku'] ?? data_get($product, 'sku'),
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
        $this->items->push($orderItem);
        $this->computeOrderAmount();

        // clear
        $this->temp = [];
        // return $orderItem;
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
     * @return void
     */
    public function save ()
    {
        $isNew = !$this->order()->id;
        if ($isNew && !isset($this->order()->user_id)) {
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
     * @param  string  $description
     * @param  int  $amount 可选，指定支付金额，默认订单金额
     * @return self
     */
    public function charge (string $gatewayCode, string $description = null, int $amount = null)
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
        ]);

        try {
            $gateway = $this->getGateway($gatewayCode, $payment->id);
            $gateway->onCharge($order, $description ?? "支付订单#{$payment->order_id}", $payment->amount);

            // update...
            $this->updatePaymentFromGateway($payment, $gateway);
            $this->updateOrderAfterPaid($payment);
        } catch (Exception $e) {
            $payment->update(['status' => 'failure', 'comment' => substr($e->getMessage(), 0, 255),]);
            throw $e;
        }

        return $this;
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
            $payment->update(['status' => 'failure', 'comment' => substr($e->getMessage(), 0, 255),]);
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
            return new $className($gatewayCode, $payment_id);
        }
        throw new Exception('invalid gateway: ' . $gateway);
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
        $data = $gateway->getTransactionData();
        if ($data) {
            $payment->data = $data;
        }
        $transaction_id = $gateway->getTransactionId();
        if ($transaction_id) {
            $payment->transaction_id = $transaction_id;
        }
        $transaction_status = $gateway->getTransactionStatus();
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
     * to array
     * 
     * @return array
     */
    public function toArray ()
    {
        $items = [];
        foreach ($this->items as $item) {
            $items[] = $item->toArray();
        }
        return [
            'order' => $this->order ? $this->order->toArray() : null,
            'items' => $items,
        ];
    }

    /**
     * to string
     * 
     * @return string
     */
    public function __toString ()
    {
        $order = $this->order();
        $items = $this->items();

        $lines = [];
        $lines[] = "\n/**********************";
        $lines[] = "【联系信息】";
        $lines[] = implode(' ', [data_get($order, 'contacts.name'), data_get($order, 'contacts.telephone')]);
        $lines[] = data_get($order, 'contacts.address_regions');
        $lines[] = data_get($order, 'contacts.address_detail');
        $lines[] = "\n【产品明细】";
        $items = collect($items)->sortBy('group');
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
                $lines[] = implode("  ", [
                    $item->name,
                    $item->qty ? "x {$item->qty}{$unit}" : '',
                    $item->row_total ? number_format($item->row_total / 100, 2) . "元" : '',
                ]);
            }
        }
        $lines[] = "\n【费用】";
        // $lines[] = "小计" . number_format(data_get($order, 'subtotal') / 100, 2) . "元";
        $lines[] = "总计" . number_format(data_get($order, 'grand_total') / 100, 2) . "元";
        $lines[] = "**********************/";
        return implode("\n", $lines);
    }
}
