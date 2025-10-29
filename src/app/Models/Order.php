<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_number',
        'user_id',
        'customer_phone',
        'customer_address',
        'total_amount',
        'discount_amount',
        'pay_amount',
        'payment_method',
        'status',
        'paid_at',
        'shipped_at',
        'completed_at',
        'currency',
        'remarks',
    ];

    protected $attributes = [
        'currency' => 'CNY',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}

