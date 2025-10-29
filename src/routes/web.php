<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');

//Route::resource('orders', OrderController::class);

Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');

Route::delete('/orders/{id}', [OrderController::class, 'destroy'])->name('orders.destroy');

Route::post('/orders/{order}/status', [OrderController::class, 'updateStatus']);
