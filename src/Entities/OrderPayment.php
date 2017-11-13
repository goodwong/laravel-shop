<?php

namespace Goodwong\LaravelShop\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderPayment extends Model
{
    use SoftDeletes;

    /**
     * table name
     */
    protected $table = 'shop_order_payments';

    /**
     * fillable fields
     */
    protected $fillable = [
        'order_id',
        'amount',
        'gateway',
        'status',
        'comment',
        'data',
        'paid_at',
    ];
    
    /**
     * date
     */
    protected $dates = [
        'paid_at',
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
