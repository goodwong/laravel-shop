<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type', 16)->comment('区分业务逻辑');
            $table->integer('order_id')->unsigned()->comment('订单id');
            $table->string('shop_id', 16)->nullable()->comment('商家id，如 15 或service_plan');
            $table->string('product_id', 16)->nullable()->comment('产品id，如 2837，或plan_B等');
            $table->string('group', 16)->nullable()->comment('用于展示时的分组，如：“五金店”、“配件”，系统会按group排序');
            $table->string('name', 32)->comment('产品名称');
            $table->string('sku', 32)->nullable()->comment('商品统一编码');
            $table->integer('price')->nullable()->comment('原始单价，不含选项、不含折扣、锐、运费等，有可能为负数');
            $table->integer('qty')->nullable()->comment('数量，不计数量为null，如coupon“业主推荐 -500元”');
            $table->string('unit', 16)->nullable()->comment('计数单位');
            $table->integer('row_total')->nullable()->comment('行小计，如装修里的特殊要求，免费');
            $table->string('comment')->nullable()->comment('用户留言');
            $table->jsonb('data')->nullable()->comment('数据，根据type定义');
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
        Schema::dropIfExists('order_items');
    }
}
