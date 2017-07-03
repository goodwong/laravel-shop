# Laravel 5 Shop

基本的商家、订单结构

> 订单可以独立使用

## 安装

## 数据结构

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
```php
echo app('Goodwong\LaravelShop\Handlers\OrderHandler')
->appendItem(['name' => 'xxxx', 'price' => 1500], 1, ['group' => 'cloth'])
->appendItem(['name' => 'fruit', 'price' => 200, 'unit' => 'L'], 2, ['group' => 'others'])
->appendItem(['name' => 'no-juice', 'price'=>1508], null, ['group' => 'others'])
->appendItem(['name'=>'apple'], 15, ['group' => 'others'])
->setContacts(['name'=>'william', 'telephone'=>'13510614266', 'address'=>'nanshan district, shenzhen city'])
->setUserId(1);
// 【联系信息】
// william 13510614266
// nanshan district, shenzhen city
// 
// 【产品明细】
// === cloth ===
// xxxx  x 1  15.00元
// === others ===
// fruit  x 2L  4.00元
// no-juice
// apple  x 15
// 
// 【费用】
// 小计19.00元
// 总计19.00元
```

## RESTful接口规范
