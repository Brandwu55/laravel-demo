<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // ✅ 订单主表增加 currency
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'currency')) {
                $table->string('currency', 10)->default('CNY')->after('total_amount');
            }
        });

        // ✅ 明细表增加 currency
        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_items', 'currency')) {
                $table->string('currency', 10)->default('CNY')->after('quantity');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('currency');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('currency');
        });
    }
};

