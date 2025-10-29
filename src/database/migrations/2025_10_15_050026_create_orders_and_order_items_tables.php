<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 主表：orders
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 50)->unique()->comment('订单号');
            $table->unsignedBigInteger('user_id')->nullable()->comment('下单用户ID');
            $table->string('customer_name', 100)->nullable()->comment('客户姓名');
            $table->string('customer_phone', 20)->nullable()->comment('客户电话');
            $table->string('customer_address', 255)->nullable()->comment('收货地址');
            $table->decimal('total_amount', 10, 2)->default(0)->comment('总金额');
            $table->decimal('discount_amount', 10, 2)->default(0)->comment('优惠金额');
            $table->decimal('pay_amount', 10, 2)->default(0)->comment('实付金额');
            $table->string('payment_method', 50)->nullable()->comment('支付方式');
            $table->enum('status', ['pending', 'paid', 'shipped', 'completed', 'cancelled'])
                  ->default('pending')->comment('订单状态');
            $table->timestamp('paid_at')->nullable()->comment('支付时间');
            $table->timestamp('shipped_at')->nullable()->comment('发货时间');
            $table->timestamp('completed_at')->nullable()->comment('完成时间');
            $table->text('remarks')->nullable()->comment('备注');
            $table->timestamps();

            $table->index('user_id');
        });

        // 明细表：order_items
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->comment('订单ID');
            $table->string('product_name', 100);
            $table->integer('quantity')->default(1);
            $table->decimal('price', 10, 2)->default(0);
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};

