<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shop_orders', function (Blueprint $table) {
            $table->increments('id');
            $table->string('context', 32)->nullable()->comment('如：shop_15、project_15、recharge');
            $table->integer('user_id')->unsigned()->comment('用户id');
            $table->jsonb('contacts')->nullable()->comment('联系人信息');
            $table->string('comment')->nullable()->comment('用户评论');
            $table->string('status', 16)->default('new')->comment('订单状态');
            $table->string('currency', 16)->default('CNY')->comment('币种，默认 CNY');
            $table->integer('subtotal')->unsigned()->default(0)->comment('小计：不含运费、税');
            $table->integer('grand_total')->unsigned()->default(0)->comment('总计');
            $table->integer('shipping_amount')->unsigned()->default(0)->comment('运费');
            $table->integer('discount_amount')->unsigned()->default(0)->comment('折扣');
            $table->integer('paid_total')->unsigned()->default(0)->comment('已支付金额');
            $table->jsonb('records')->nullable()->comment('订单动态记录');
            $table->timestamp('expected_at')->nullable()->comment('预约时间');
            $table->timestamp('finished_at')->nullable()->comment('实际完成时间');
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
        Schema::dropIfExists('shop_orders');
    }
}
