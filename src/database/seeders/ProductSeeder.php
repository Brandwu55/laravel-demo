<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        Product::insert([
            [
                'name' => '牙齿矫正套装',
                'currency' => 'CNY',
                'price' => 2999.00,
                'description' => '适合青少年使用的标准矫正套装。',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '专业牙刷套装',
                'currency' => 'USD',
                'price' => 49.99,
                'description' => '美国进口，柔软刷毛。',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '口腔护理喷雾',
                'currency' => 'EUR',
                'price' => 19.50,
                'description' => '便携型口腔清新喷雾。',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
