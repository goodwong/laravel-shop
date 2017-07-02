<?php

namespace Goodwong\LaravelShop\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    /**
     * table name
     */
    protected $table = 'orders';

    /**
     * fillable fields
     */
    protected $fillable = [
        'context',
        'user_id',
        'contacts',
        'comment',
        'status',
        'subtotal',
        'grand_total',
        'shipping_amount',
        'discount_amount',
        'paid_total',
        'records',
        'expected_at',
        'finished_at',
    ];
    
    /**
     * date
     */
    protected $dates = [
        'expected_at',
        'finished_at',
        'deleted_at',
    ];

    /**
     * cast attributes
     */
    protected $casts = [
        'contacts' => 'object',
        'records' => 'array',
    ];
    //
}