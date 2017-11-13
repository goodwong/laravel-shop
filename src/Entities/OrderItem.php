<?php

namespace Goodwong\LaravelShop\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderItem extends Model
{
    use SoftDeletes;

    /**
     * table name
     */
    protected $table = 'shop_order_items';

    /**
     * fillable fields
     */
    protected $fillable = [
        'type',
        'order_id',
        'shop_id',
        'product_id',
        'group',
        'name',
        'sku',
        'price',
        'qty',
        'unit',
        'row_total',
        'comment',
        'data',
    ];
    
    /**
     * date
     */
    protected $dates = [
        'deleted_at',
    ];

    /**
     * cast attributes
     */
    protected $casts = [
        'data' => 'object',
    ];

    //
}
