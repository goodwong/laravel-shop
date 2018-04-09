# Laravel 5 Shop

基本的商家、订单结构 **`（未完成）`**

> 1. 产品图片上传依赖 php-gd 扩展，依赖 Intervention Image 包

## 特色
> 1. 链式调用，开发者友好
> 2. 支持多种商品对象，也可无需对象自定义添加数据
> 3. 字段灵活，分类、SKU、评论、自定义选项都支持
> 4. 添加支付网管灵活简单，1个文件+1个配置，即可写好一种支付方式
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
实例化
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

添加项
> 1. 不会自动合并商品
> 2. 商品可以是 Product对象实例，也可以是自定义商品
> 3. 必填字段是 name

```php
// 简单操作
$shopping
    ->product($product)
    ->add($qty = 1);

$shopping
    ->name('配送费')
    ->rowTotal(1500)
    ->add(); // qty = null 不会自动计价


// 添加产品（完整参数)
$shopping
    ->group('饮品')
    // 传入product对象，
    // type/shop/name/sku/price/product_id/row_total 自动设置
    // 后面还可以覆盖这些属性
    ->product($product)
    ->unit('斤')
    ->rowTotal(15000) // 覆盖 price * qty
    // 自定义选项内容（格式自定）
    // e.g. {'配菜': {'泡椒': { price:500, sku:'PJ', qty:2 }, '酸笋': { price:500, sku:'SS', qty:2 }} }
    ->specs($specs)
    ->comment('去冰，加点辣椒')
    ->add();

// 自定义添加（完整参数)
$shopping
    ->type('custom')
    ->group('饮品')
    ->shop('service')
    ->productId($product)
    ->name('xx') // 必填
    ->sku('xx')
    ->price('xx')
    ->unit('斤')
    ->rowTotal(15000) // 默认 price * qty
    ->specs($specs) 
    ->comment('......')
    ->add($qty = 1);


// 例子：优惠抵扣
$shopping
    ->type('discount')
    ->name('优惠券抵扣')
    ->rowTotal(-15000)
    ->add();

// 例子：优惠抵扣
$shopping
    ->type('tax')
    ->name('税费%3')
    ->rowTotal(550)
    ->add();

```

设置信息
```php
// 联系方式（格式自定）
$shopping->contacts([
    'name'=>'william',
    'telephone'=>'135****4266',
    'address.region'=> '江西省 会昌县 西江镇',
    'address.detail' => '饼丘村12号',
]);
// 用户（可选）
$shopping->user(1);
// 留言
$shopping->orderComment($comment = 'some comments...');
// 状态
$shopping->status($status = 'paying');
// 日志
$shopping->record('place holder...');
```

保存 & 载入
```php
$shopping->save();
$shopping->load($order_id);
```

支付
> `$gateway_code` 需要在 `config/shop.php` 预先配置
```php
// 默认全额支付
$shopping->charge($gateway_code = 'wxpay_native', $brief = '小农家-会员充值');
// 也可以指定支付金额$amount
$shopping->charge($gateway_code = 'wxpay_native', $brief = null, $amount = null);
```

支持链式调用
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

其它
```php
// 订单
$shopping->order();
// 订单明细
$shopping->items();
// 支付明细
$shopping->payments();

// 打印（调试用）
echo $shopping;
/**********************
  【地址】
  老小王 135****4266
  江西省 会昌县 马甲镇
  冰球村 122号
  
  【产品明细】
  --- 水果 ---
  苹果  x 1斤  15.00元
  --- 蔬菜 ---
  鸡蛋  x 2个   4.00元
  紫甘蓝 x 2颗 18.00元
  打包   x 3   25.00元
  --- 其它 ---
  运费         5.00元
  会员优惠     -3.60元
  
  【费用】
  总计        19.00元
**********************/
```


## 配置

`config/shop.php`

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


## 创建自定义支付网关

1. 实现接口 `Goodwong\Shop\Contracts\GatewayInterface`
2. 建议继承 `Goodwong\Shop\Gateways\GatewayBase`
3. 添加到 `config/shop.php` 的 `gateways`数组



