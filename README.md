# Laravel 5 Shop

基本的商家、订单结构（未完成）

> 依赖 php-gd 扩展
> 依赖 Intervention Image 包


> 订单可以独立使用

## 安装
```shell
composer require goodwong/laravel-shop
```

2. 创建配置文件：
```shell
php artisan vendor:publish --provider="Goodwong\LaravelShop\ShopServiceProvider"
```

## 数据结构
（待整理）

## 场景

1. 商品订单
- type 默认为 product
- shop_id 为 商品里的shop_id值
- product_id 为商品id值
- data 商品选项的值，如 {'配菜': {'泡椒': { price:500, sku:'PJ', qty:2 }, '酸笋': { price:500, sku:'SS', qty:2 }} }

2. 服务订单：续订服务、充值订单等
- type 由业务定义，如 plan，**用作业务分类处理的识别依据**
- shop_id 可以设置为字符串，如 service_plan
- product_id 可以设置为字符串，如 plan_B，付款后，作为判断依据
- data 由业务定义，如 { start_at, days }

3. 特殊费用
- type  由业务定义，如 fee
- shop_id 无
- product_id 无
- data 无

4. 特殊要求（但无费用）
- type 如 requirement
- name 需求名称

## 使用
实例化
```php
$handler = app('Goodwong\LaravelShop\Handlers\OrderHandler');
// 也可以使用DI方式注入
public function __construct(Goodwong\LaravelShop\Handlers\OrderHandler $handler) {
	$this->orderHandler = $handler;
}
// 实例化已有订单
$handler = app('Goodwong\LaravelShop\Handlers\OrderHandler')->load($order_id);
```

添加项
> 不会自动合并商品
> 商品可以是 Product对象实例，也可以只是数组。必填字段是 name（参考数据规范）

```php
// 简单
$handler->appendItem($product = ['name' => 'xxxx', 'price' => 1500], $qty = 1);
// qty 可以省略，默认为1
$handler->appendItem($product = ['name' => 'xxxx', 'price' => 1500]);
// 可以分组
$handler->appendItem(['name' => 'xxxx', 'price' => 1500], 1, ['group' => '配件']);
// 商品单位
$handler->appendItem(['name' => 'fruit', 'price' => 200, 'unit' => 'L', 'sku' => 'FRUIT-APPLE-001']);
// 不要价格
$handler->appendItem(['name'=>'apple'], 15, ['group' => 'others']);
```

其它用法
```php
// 自定义费用
$handler->appendItem(['name' => '配送费'], null, ['group' => '费用', 'row_total' => 1500, ]);
$handler->appendItem(['name' => '打包费'], null, ['group' => '费用', 'row_total' => 100, ]);
```

~~更新项（未完成）~~
```php
$handler->updateItem($item, $quantity, $attributes = []);
```

设置信息
```php
// 联系方式
$handler->setContacts(['name'=>'william', 'telephone'=>'13510614266', 'address'=>'nanshan district, shenzhen city']);
// 设置用户
$handler->setUserId(1);
// 设置用户留言
$handler->setComment($comment = 'some comments...');
// 设置状态
$handler->setStatus($status = 'paying');
// 添加日志
$handler->record('place holder...');
```

支付
```php
// 默认全额支付，也可以指定支付金额$amount
$handler->charge($gateway_code, $brief = null, $amount = null);
```

持久化
```php
$handler->save();
$handler->load($order_id);
```

链式调用
```php
echo app('Goodwong\LaravelShop\Handlers\OrderHandler')
->appendItem(['name' => 'xxxx', 'price' => 1500], 1, ['group' => 'cloth'])
->appendItem(['name' => 'fruit', 'price' => 200, 'unit' => 'L'], 2, ['group' => 'others'])
->appendItem(['name' => 'no-juice', 'price'=>1508], null, ['group' => 'others'])
->appendItem(['name'=>'apple'], 15, ['group' => 'others'])
->setContacts(['name'=>'william', 'telephone'=>'13510614266', 'address'=>'nanshan district, shenzhen city'])
->setUserId(1)
->record('place order ...')
->save()
->charge('wxpay_native', 'iPad mini 4');
```

其它
```php
$handler->getOrder();
$handler->getItems();
$handler->toArray();

// 测试用
(string)$handler;
/****************************
  【联系信息】
  william 13510614266
  nanshan district, shenzhen city
  
  【产品明细】
  === cloth ===
  xxxx  x 1  15.00元
  === others ===
  fruit  x 2L  4.00元
  no-juice
  apple  x 15
  
  【费用】
  小计19.00元
  总计19.00元
****************************/
```

## RESTful接口规范
（待整理）


## 配置

config/shop.php

- payment_callback_route
    回调路由

- gateways
    网关列表


## 自定义支付网关

1. 继承 Goodwong\LaravelShopGatewayWxpay\



