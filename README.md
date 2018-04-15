# Laravel 5 Shop

基本的商家、订单结构

> 1. 产品图片上传依赖 php-gd 扩展，依赖 Intervention Image 包

## 特色
> 1. 链式调用，开发者友好
> 2. 支持多种商品对象，也可无需对象自定义添加数据
> 3. 字段灵活，分类、SKU、评论、自定义选项都支持
> 4. 添加支付网管灵活简单，1个文件+1个配置，即可写好一种支付方式
> 5. 支持添加自定义各种税费


## 安装
1. 添加 子模块
```shell
git submodule add https://github.com/goodwong/laravel-shop.git .packages/goodwong/laravel-shop
```

2. 添加composer配置
```json
    "goodwong/laravel-shop": "dev-master",
```

3. 创建配置文件（用于配置支付网关）：
```shell
composer update
php artisan vendor:publish --provider="Goodwong\Shop\ShopServiceProvider"
```


## 使用

### 实例化
```php
$shopping = app('Goodwong\Shop\Shopping');

// 也可以使用DI方式注入
public function __construct(Goodwong\Shop\Shopping $shopping)
{
	$this->shopping = $shopping;
}

// 载入已有订单
$shopping->load($order_id);
```

### 添加项
> 1. 不会自动合并商品
> 2. 商品可以是 Product对象实例，也可以是自定义商品
> 3. 必填字段是 name
> 4. 金额的单位是分
> 5. 没有指定 `add()` 数量时，不会自动计算 `rowTotal`，可以自行设置
> 6. 自定义选项影响价格计算时，需要自行计算并设置 `rowTotal`

```php
// 简单操作
$shopping
    ->withProduct($product)
    ->add($qty = 1);

$shopping
    ->name('配送费')
    ->rowTotal(1500) // 15元
    ->add(); // qty = null 不会自动计价


// 添加产品（完整参数)
$shopping
    ->group('饮品')
    // 传入product对象，
    // 自动设置 type/shop_id/product_id/sku/group/name/price/unit（除了qty/comment/specs），自动计算row_total
    // 后面还可以覆盖这些属性
    ->withProduct($product)
    ->unit('斤')
    ->rowTotal(15000) // 覆盖 price * qty
    // 自定义选项内容（结构自定），使用嵌套数据表示
    // e.g. {'配菜': {'泡椒': { price:500, sku:'PJ', qty:2 }, '酸笋': { price:500, sku:'SS', qty:2 }} }
    ->specs($specs)
    ->comment('去冰，加点辣椒')
    ->add();

// 自定义添加（完整参数)
$shopping
    ->type('service')
    ->group('服务')
    ->shop('service')
    ->product($productId)
    ->name('洗车+全套护理') // 必填
    ->sku('SRV-SZ-CLN-009-PLUS-502')
    ->price(58000)
    ->unit('次')
    ->rowTotal(96000) // 默认 price * qty，此处打折扣
    ->specs($specs) // 详细参数
    ->comment('特制皮具，需使用专用护理液')
    ->add($qty = 2);


// 例子：优惠抵扣
$shopping
    ->type('discount')
    ->name('优惠券抵扣')
    ->rowTotal(-15000)
    ->add();

// 例子：税费
$shopping
    ->type('tax')
    ->name('税费 3%')
    ->rowTotal(550)
    ->add();

```

### 设置订单信息
```php
// 联系方式（格式自定）
$shopping->contacts([
    'name'=>'william',
    'telephone'=>'135****4266',
    'address.region'=> '江西省 会昌县 西江镇',
    'address.detail' => '饼丘村12号',
]);

// 用户（可选，新订单默认当前用户）
$shopping->user(1);

// 留言
$shopping->orderComment($comment = 'some comments...');

// 状态
$shopping->status($status = 'paying');

// 日志
$shopping->record('place holder...');
```

### 保存 & 载入
```php
// 保存
$shopping->save();

// 从数据库载入
$shopping->load($order_id);
```

### 支付
> `$gateway_code` 需要在 `config/shop.php` 预先配置
```php
// 默认全额支付
$shopping->charge($gateway_code = 'wxpay_native');

// 也可以指定支付金额$amount
$shopping->charge($gateway_code = 'wxpay_native', $amount = 18800);

// 带上其它支付网管需要的参数（不同支付网关需要的参数不同）
$shopping->charge($gateway_code = 'wxpay_jsapi', $amount = 18800, [ 'title' => '小农家商店', 'openid' => 'xxxxlxxxxlxxxxlxxxxlxxxx']);
```

### 支持链式调用
```php
app('Goodwong\Shop\Shopping')
    ->product($product)->add(1);
    ->name('配送费')->rowTotal(1500)->add();
    ->contacts(['name'=>'william', 'telephone'=>'135****4266', ])
    ->user(1)
    ->record('place order ...')
    ->save()
    ->charge('wxpay_native', 'iPad mini 4');
```

### 其它
```php
// 订单
$shopping->order();

// 订单明细
$shopping->items();

// 支付明细
$shopping->payments();

// 打印（调试用）
$shopping = app('Goodwong\Shop\Shopping');
$shopping->contacts([
    'name' => '老小王',
    'telephone' => '135****4266',
    'address_regions' => '江西省 会昌县 马甲镇',
    'address_detail' => '冰球村122号',
]);
$shopping->type('product')->shop('fruit')->group('水果')->name('苹果')->price(1500)->unit('斤')->add(1);
$shopping->type('product')->shop('vegetable')->group('蔬菜')->name('鸡蛋')->price(200)->unit('个')->comment('请帮忙多套几个袋子，谢谢')->add(2);
$shopping->type('product')->shop('vegetable')->group('蔬菜')->name('带水晶香味的长白山紫甘蓝')->price(1000)->unit('颗')->rowTotal(1800)->comment('9折')->add(2);
$shopping->type('product')->shop('service')->group('其它')->name('打包')->rowTotal(2500)->add(3);
$shopping->type('product')->shop('service')->group('其它')->name('运费')->rowTotal(500)->add();
$shopping->type('product')->shop('fee')->group('其它')->name('会员优惠')->rowTotal(-360)->add();

echo $shopping->print();
// 输出：
/**********************
【联系信息】
老小王 135****4266
江西省 会昌县 马甲镇
冰球村122号

【产品明细】
--- 水果 ---
·苹果 x1斤     15.00元
--- 蔬菜 ---
·鸡蛋 x2个
(请帮忙多套几个袋子，谢
谢)             4.00元
·带水晶香味的长白山紫甘
蓝 x2颗
(9折)          18.00元
--- 其它 ---
·打包 x3       25.00元
·运费           5.00元
·会员优惠      -3.60元

【费用】
总计63.40元
**********************/

// 有实现 _toString()，因此可以在Controller里面
return $shopping;
```


## 配置

### 修改配置文件 `config/shop.php`

```php
// 回调路由
'payment_callback_route' => env('SHOP_PAYMENT_CALLBACK_ROUTE', 'order-payments.callback'),

// 网关列表
'gateways' => [
    'wxpay_native' => \Goodwong\ShopGatewayWxpay\GatewayWxpayNative::class,
    'wxpay_h5' => \Goodwong\ShopGatewayWxpay\GatewayWxpayH5::class,
    'alipay_h5' => \Goodwong\ShopGatewayWxpay\GatewayAlipayH5::class,
    // ...
],
```


### 创建自定义支付网关

#### 三部曲
> 1. 继承 `Goodwong\Shop\Gateways\GatewayBase`
> 2. 或者实现接口 `Goodwong\Shop\Contracts\GatewayInterface`
> 3. 添加到 `config/shop.php` 的 `gateways`数组

#### `GatewayBase` 代码细节
> 1. 最重要的是实现`onCharge()`、`onCallback()`这两个方法
> 1. 稍微了解传入参数 `Order` 对象，主要是 `$order->id`和`$order->grand_total`。
> 2. 一个订单号有可能多次发起支付，每次发起支付建议使用`$this->getSerialNumber($order)`生成商家流水号。
> 4. 使用`$this->setTransactionId($serial_number)` 将流水号传递到订单系统。
```php
$serial_number = $this->getSerialNumber($order);
// 使用 $serial_number 发起支付请求……
$this->setTransactionId($serial_number);
```
> 3. 发起支付、支付完成、支付失败，通过以下方法将信息传递到订单系统就好啦，其他不用管。
```php
$this->setTransactionData($result);
$this->setTransactionStatus('failure'); // success | failure
```

#### 举个例子……
[goodwong/laravel-shop-gateway-wxpay](https://github.com/goodwong/laravel-shop-gateway-wxpay)，是真的可以用的喔～


## 更多功能（待实现……）
> 1. 优惠券（限制：限商店、限品类、限单品，类型：折扣券、兑换券）
> 2. 支付成功后（需要全款）的订单状态可以在配置文件里设置
