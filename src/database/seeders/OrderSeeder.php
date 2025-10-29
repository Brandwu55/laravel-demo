<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Order;
use App\Models\OrderItem;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        // 示例订单数据
        $orders = [
            [
                'user_id' => 1,
                'customer_name' => '张三',
                'customer_phone' => '13800000000',
                'customer_address' => '上海市徐汇区龙华街道88号',
                'payment_method' => 'wechat',
                'status' => 'paid',
                'remarks' => '首次下单',
                'items' => [
                    ['product_name' => '义齿模型', 'quantity' => 2, 'price' => 120],
                    ['product_name' => '修复材料', 'quantity' => 1, 'price' => 80],
                ],
            ],
            [
                'user_id' => 2,
                'customer_name' => '李四',
                'customer_phone' => '13900001111',
                'customer_address' => '北京市朝阳区望京东路99号',
                'payment_method' => 'alipay',
                'status' => 'shipped',
                'remarks' => '老客户复购',
                'items' => [
                    ['product_name' => '牙科设备', 'quantity' => 1, 'price' => 300],
                    ['product_name' => '义齿树脂', 'quantity' => 3, 'price' => 50],
                ],
            ],
        ];

        foreach ($orders as $data) {
            $order = Order::create([
                'order_number' => 'ORD-' . strtoupper(Str::random(8)),
                'user_id' => $data['user_id'],
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'customer_address' => $data['customer_address'],
                'payment_method' => $data['payment_method'],
                'status' => $data['status'],
                'remarks' => $data['remarks'],
                'total_amount' => collect($data['items'])->sum(fn($i) => $i['price'] * $i['quantity']),
                'pay_amount' => collect($data['items'])->sum(fn($i) => $i['price'] * $i['quantity']),
            ]);

            foreach ($data['items'] as $item) {
                $order->items()->create($item);
            }
        }
    }
}

