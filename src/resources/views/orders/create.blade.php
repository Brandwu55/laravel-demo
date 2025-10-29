@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Create New Order</h1>

        <form method="POST" action="{{ route('orders.store') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Customer Name</label>
                <input type="text" name="customer_name" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Order Number</label>
                <input type="text" name="order_number" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Total Amount</label>
                <input type="number" name="total_amount" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-success">Save</button>
            <a href="{{ route('orders.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
@endsection
