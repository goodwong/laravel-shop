<?php

namespace Goodwong\LaravelShop\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    /**
     * table name
     */
    protected $table = 'shop_products';

    /**
     * fillable fields
     */
    protected $fillable = [
        'shop_id',
        'category_id',
        'slug',
        'name',
        'sku',
        'price',
        'position',
        'settings',
        'options',
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
        'settings' => 'object',
        'options' => 'object',
    ];

    //
}
