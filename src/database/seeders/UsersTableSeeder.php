<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::truncate();

        User::insert([
            ['name' => '张三', 'email' => 'zhangsan@example.com', 'password' => '13800000001'],
            ['name' => '李四', 'email' => 'lisi@example.com', 'password' => '13800000002'],
            ['name' => '王五', 'email' => 'wangwu@example.com', 'password' => '13800000003'],
        ]);
    }
}
