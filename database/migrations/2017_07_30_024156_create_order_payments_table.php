<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shop_order_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('order_id')->unsigned()->comment('订单id');
            $table->integer('amount')->comment('支付/退款金额');
            $table->string('gateway')->comment('支付网关，如：wxpay_native');
            $table->string('transaction_id')->nullable()->comment('网关返回的流水号/退单号，若现金支付则留空');
            $table->string('status')->default('pendding')->comment('支付状态，如：pendding 等待支付 | success 成功 | failure 失败');
            $table->string('comment')->nullable()->comment('一般记录支付失败原因');
            $table->jsonb('data')->nullable();
            $table->timestamps();
            $table->timestamp('paid_at')->nullable()->comment('支付时间');
            $table->softDeletes();

            $table->foreign('order_id')
                  ->references('id')->on('shop_orders')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shop_order_payments', function (Blueprint $table) {
            $table->dropForeign('shop_order_payments_order_id_foreign');
        });

        Schema::dropIfExists('shop_order_payments');
    }
}
