<?php

/**
 * 订单操作
 * 
 * 不计算产品选项价格
 * 不检验库存
 * 以上逻辑请业务方自行处理
 */

namespace Goodwong\LaravelShop\Handlers;

use Illuminate\Http\Request;
use Goodwong\LaravelShop\Entities\Order;
use Goodwong\LaravelShop\Entities\OrderItem;
use Goodwong\LaravelShop\Entities\OrderPayment;

class OrderHandler
{
    /**
     * @var Order $order
     */
    var $order = null;

    /**
     * @var array $items <OrderItem>
     */
    var $items = [];

    /**
    * get order
    * 
    * @return Order
    */
    public function getOrder()
    {
        if (!$this->order) {
            $this->order = new Order;
        }
        return $this->order;
    }

    /**
     * get items
     * 
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * load order with items
     * 
     * @param  integer  $order_id
     * @return this
     */
    public function load($order_id)
    {
        $this->order = Order::findOrFail($order_id);
        $this->order->load('items');
        $this->items = $this->order->items;
        return $this;
    }
    /**
     * magic method
     *
     * @param $method string
     * @param $args array
     *
     * @return mixed
     */
    public function __call($method, array $args)
    {
        if (method_exists($this, $method)) {
            return call_user_func_array(array($this, $method), $args);
        }
        if (starts_with($method, 'set')) {
            $order = $this->getOrder();
            $attribute = snake_case(substr($method, 3));
            $order->$attribute = $args[0];
            return $this;
        }
        throw new \Exception("Call to undefined method" . self::class . "::" . $method . '()');
    }

    /**
     * append record
     * 
     * @param  string  $content
     * @return this
     */
    public function record($content)
    {
        $order = $this->getOrder();
        $records = (array)$order->records;
        $records[] = [
            'content' => $content,
            'timestamp' => date('Y/m/d H:i:s'),
        ];
        $order->records = $records;
        return $this;
    }

    /**
     * append order item. 
     * 
     * 产品选项里的价格需要调用者计算，若没有row_total，则自动计算 product.price * qty
     * 
     * @param  array  $product { name }
     * @param  integer  $quantity
     * @param  array  $attributes  { type, group, row_total, data, comment, }
     * @return this
     */
    public function appendItem($product, $quantity = null, $attributes = [])
    {
        $type = data_get($attributes, 'type', 'product');
        $shop_id = data_get($product, 'shop_id');
        $product_id = data_get($product, 'id');
        $group = data_get($attributes, 'group');
        $name = data_get($product, 'name');
        $sku = data_get($product, 'sku');
        $price = data_get($product, 'price');
        $qty = $quantity;
        $unit = data_get($product, 'unit');
        $row_total = data_get($attributes, 'row_total', $price !== null && $qty !== null ? $price * $quantity : null);
        $comment = data_get($attributes, 'comment');
        $data = data_get($attributes, 'data');

        $item = new OrderItem(compact(
            'type',
            'shop_id', 'product_id', 'group', 'name', 'sku', 'price', 'unit',
            'qty', 'row_total', 'comment', 'data'
        ));
        $this->items[] = $item;

        // update order
        $this->updateOrderAmount();
        return $this;
    }

    /**
     * update item
     * 
     * @param  OrderItem  $item
     * @param  integer  $quantity
     * @param  array  $attributes
     * @return void
     */
    public function updateItem($item, $quantity, $attributes = [])
    {
        // ...

        // update order
        $this->updateOrderAmount();
    }

    /**
     * save order and it's items
     * 
     * @return this
     */
    public function save()
    {
        $this->updateOrderAmount();
        $this->order->save();

        $items = $this->getItems();
        foreach ($items as $item) {
            $item->order_id = $this->order->id;
            $item->save();
        }
        return $this;
    }

    /**
     * update order amount
     */
    private function updateOrderAmount()
    {
        $order = $this->getOrder();
        $items = $this->getItems();

        $subtotal = 0;
        foreach ($items as $item) {
            if ($item->row_total) {
                $subtotal += $item->row_total;
            }
        }
        $order->subtotal = $subtotal;
        $order->grand_total = $subtotal + (int)$order->shipping_amount + (int)$order->discount_amount;
    }

    /**
     * charge
     * 
     * @param  string  $gateway_code
     * @param  string  $brief
     * @return this
     */
    public function charge($gateway_code, $brief = null)
    {
        $order = $this->getOrder();
        if (!$order->id) {
            throw new \Exception('order not saved');
        }
        $payment = OrderPayment::create([
            'order_id' => $order->id,
            'amount' => $order->grand_total,
            'gateway' => $gateway_code,
        ]);
        try {
            $gateway = $this->getGateway($gateway_code, $payment->id);
            $gateway->onCharge($order, $brief);

            $payment->data = $gateway->getTransactionData();
            $payment->transaction_id = $gateway->getTransactionId();
            $payment->status = $gateway->getTransactionStatus();
            if ($payment->status === 'success') {
                $payment->paid_at = date('Y-m-d H:i:s');
            }
        } catch (\Exception $e) {
            $payment->status = 'failure';
            $payment->comment = $e->getMessage();
        }
        $payment->save();
        return $this;
    }

    /**
     * callback
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  integer  $payment_id
     * @return Response
     */
    public function callback(Request $request, $payment_id)
    {
        $payment = OrderPayment::findOrFail($payment_id);
        try {
            $gateway = $this->getGateway($payment->gateway, $payment->id);
            $response = $gateway->onCallback($request);

            $payment->transaction_id = $gateway->getTransactionId();
            $payment->status = $gateway->getTransactionStatus();
            if ($payment->status === 'success') {
                $payment->paid_at = date('Y-m-d H:i:s');
            }
            $payment->save();
            return $response;
        } catch (\Exception $e) {
            $payment->status = 'failure';
            $payment->comment = $e->getMessage();
            $payment->save();
            abort(500, $e->getMessage());
        }
    }

    /**
     * get gateway
     * 
     * @param  string  $gateway_code
     * @param  integer  $payment_id
     * @return  Gateway
     */
    private function getGateway($gateway_code, $payment_id)
    {
        $className = config("shop.gateways.{$gateway_code}");
        if ($className && class_exists($className)) {
            return new $className($gateway_code, $payment_id);
        }
        throw new \Exception('invalid gateway: ' . $gateway);
    }

    /**
     * to array
     * 
     * @return array
     */
    public function toArray()
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
    public function __toString()
    {
        $order = $this->getOrder();
        $items = $this->getItems();

        $lines = [];
        $lines[] = "\n【联系信息】";
        $lines[] = implode(' ', [data_get($order, 'contacts.name'), data_get($order, 'contacts.telephone')]);
        $lines[] = data_get($order, 'contacts.address');
        $lines[] = "\n【产品明细】";
        $items = collect($items)->sortBy('group');
        $groups = $items->pluck('group')->unique()->values()->all();
        foreach ($groups as $group) {
            if ($group) {
                $lines[] = "=== " . $group . " ===";
            }
            foreach ($items->values()->all() as $item) {
                if (data_get($item, 'group') === $group) {
                    $unit = data_get($item, 'unit') ? $item->unit : '';
                    $lines[] = implode("  ", [
                        $item->name,
                        $item->qty ? "x {$item->qty}{$unit}" : '',
                        $item->row_total ? number_format($item->row_total / 100, 2) . "元" : '',
                    ]);
                }
            }
        }
        $lines[] = "\n【费用】";
        $lines[] = "小计" . number_format(data_get($order, 'subtotal') / 100, 2) . "元";
        $lines[] = "总计" . number_format(data_get($order, 'grand_total') / 100, 2) . "元";
        return implode("\n", $lines);
    }
}
