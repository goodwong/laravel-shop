<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shop_products', function (Blueprint $table) {
            $table->increments('id');
            $table->string('shop_id', 16)->nullable()->comment('商家id，如 15 或service_plan');
            $table->string('category_id', 32)->nullable()->comment('分类，如 shop_3_accessories');
            $table->string('slug', 32)->nullable()->comment('一定范围内唯一，需配合shop_id/category_id使用');
            $table->string('name', 32)->comment('产品名称');
            $table->string('sku', 32)->nullable()->comment('商品统一编码');
            $table->integer('price')->comment('原始单价，不含选项、不含折扣、锐、运费等，有可能为负数');
            $table->integer('position')->default(0)->comment('产品排序用，整数，越小越前面');
            $table->jsonb('settings')->nullable()->comment('自定义');
            $table->jsonb('options')->nullable()->comment('选项');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shop_products');
    }
}
