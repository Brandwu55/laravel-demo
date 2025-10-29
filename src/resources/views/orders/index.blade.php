@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>订单列表</h2>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createOrderModal">
            創建訂單
        </button>
    </div>

    <div class="modal fade" id="createOrderModal" tabindex="-1" aria-labelledby="createOrderModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createOrderModalLabel">創建新訂單</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div id="modal-errors" class="alert alert-danger d-none"></div>

                <form id="createOrderForm">
                    @csrf

                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="user_id" class="form-label"><span class="text-danger">*</span>用户</label>
                            <select name="user_id" id="user_id" class="form-select" required>
                                <option value="">请选择用户</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="customer_phone" class="form-label"><span class="text-danger">*</span>聯繫方式</label>
                            <input type="number" name="customer_phone" id="customer_phone" class="form-control" placeholder="請輸入手機號碼">
                        </div>

                        <div class="mb-3">
                            <label for="customer_address" class="form-label"><span class="text-danger">*</span>詳細收貨地址</label>
                            <input type="text" name="customer_address" id="customer_address" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label for="payment_method" class="form-label"><span class="text-danger">*</span>支付方式</label>
                            <select name="payment_method" id="payment_method" class="form-select" required>
                                <option value="alipay">支付宝</option>
                                <option value="wechat">微信</option>
                                <option value="paypal">paypal</option>
                            </select>
                        </div>

                        <hr>

                        <h5>订单明细</h5>
                        <div id="order-items">
                            <div class="row mb-2 order-item">
                                <div class="col">
                                    <select name="items[0][product_id]" class="form-select product-select" required>
                                        <option value="">-- 请选择产品 --</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}"
                                                    data-price="{{ $product->price }}"
                                                    data-currency="{{ $product->currency }}">
                                                {{ $product->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col">
                                    <input type="number" name="items[0][product-price]" class="form-control product-price" placeholder="金额" readonly>
                                </div>
                                <div class="col">
                                    <input type="text" name="items[0][currency]" class="form-control product-currency" placeholder="币种" readonly>
                                </div>
                                <div class="col">
                                    <input type="text" name="items[0][quantity]" class="form-control product-quantity" placeholder="数量">
                                </div>
                                <div class="col-auto">
                                    <button type="button" class="btn btn-danger btn-remove-item">✖</button>
                                </div>
                            </div>
                        </div>
                        <button type="button" id="add-item" class="btn btn-sm btn-outline-primary">+ 添加明细</button>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                        <button type="button" class="btn btn-primary" id="submitOrderBtn">创建订单</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 筛选表单 -->
    <form method="GET" action="{{ route('orders.index') }}" class="mb-4">
        <div class="row">
            <div class="col-md-3">
                <input type="text" name="order_number" class="form-control" placeholder="訂單號" value="{{ request('order_number') }}">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-control">
                    <option value="">--订单状态--</option>
                    <option value="pending" {{ request('status')=='pending'?'selected':'' }}>待付款</option>
                    <option value="paid" {{ request('status')=='paid'?'selected':'' }}>已付款</option>
                    <option value="shipped" {{ request('status')=='shipped'?'selected':'' }}>已发货</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-3">
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
        </div>
        <div class="mt-3">
            <button class="btn btn-primary">筛选</button>
            <a href="{{ route('orders.index') }}" class="btn btn-secondary">重置</a>
        </div>
    </form>

    <!-- 订单列表 -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>订单号</th>
                <th>用户名稱</th>
                <th>幣種</th>
                <th>总金额</th>
                <th>支付方式</th>
                <th>创建时间</th>

                <th>状态</th>
                <th>明细</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $order)
            <tr>
                <td>{{ $order->order_number }}</td>
                <td>{{ $order->user->name }}</td>
                <td>{{ $order->currency }}</td>
                <td>{{ $order->total_amount }}</td>
                <td>{{ $order->payment_method }}</td>
                <td>{{ $order->created_at }}</td>

                <td>
                    @if($order->status === 'pending')
                        待付款
                    @elseif($order->status === 'paid')
                        已付款
                    @elseif($order->status === 'shipped')
                        已发货
                    @else
                        未知状态
                    @endif
                </td>
                <td>
                    <button
                        class="btn btn-sm btn-info"
                        data-bs-toggle="modal"
                        data-bs-target="#orderDetailModal{{ $order->id }}">
                        查看明细
                    </button>
                    <button class="btn btn-sm btn-warning"
                            onclick="editOrderStatus({{ $order->id }}, '{{ $order->status }}')">
                        修改状态
                    </button>
                    <!-- 删除按钮 -->
                    <button
                        class="btn btn-sm btn-danger btn-delete-order"
                        data-id="{{ $order->id }}">
                        删除
                    </button>
                    <!-- 弹框内容 -->
                    <div class="modal fade" id="orderDetailModal{{ $order->id }}" tabindex="-1" aria-labelledby="orderDetailModalLabel{{ $order->id }}" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="orderDetailModalLabel{{ $order->id }}">订单明细 - {{ $order->order_number }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <table class="table table-sm">
                                        <thead>
                                        <tr>
                                            <th>商品名称</th>
                                            <th>数量</th>
                                            <th>单价</th>
                                            <th>币种</th>
                                            <th>小计</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($order->items as $item)
                                            <tr>
                                                <td>{{ $item->product->name }}</td>
                                                <td>{{ $item->quantity }}</td>
                                                <td>{{ number_format($item->price, 2) }}</td>
                                                <td>{{ $item->currency }}</td>
                                                <td>{{ number_format($item->quantity * $item->price, 2) }}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                    <div class="text-end">
                                        <strong>订单总额：${{ number_format($order->total_amount, 2) }}</strong>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">关闭</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- 分页 -->
    {{ $orders->links() }}
</div>
<script>


    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-remove-item')) {
            e.target.closest('.order-item').remove();
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        // 自动带出价格 & 币种
        function attachProductSelectEvents(row) {
            const select = row.querySelector('.product-select');
            const priceInput = row.querySelector('.product-price');
            const currencyInput = row.querySelector('.product-currency');
            const quantityInput = row.querySelector('.product-quantity');


            select.addEventListener('change', function() {
                const selected = this.options[this.selectedIndex];
                priceInput.value = selected.getAttribute('data-price') || '';
                currencyInput.value = selected.getAttribute('data-currency') || '';
                quantityInput.value = 1;
            });
        }

        // 初始化第一个
        document.querySelectorAll('.order-item').forEach(attachProductSelectEvents);

        // 添加新明细
        document.getElementById('add-item').addEventListener('click', function() {
            const container = document.getElementById('order-items');
            const count = container.querySelectorAll('.order-item').length;

            const newRow = document.createElement('div');
            newRow.classList.add('row', 'mb-2', 'order-item');
            newRow.innerHTML = `
            <div class="col">
                <select name="items[${count}][product_id]" class="form-select product-select" required>
                    <option value="">-- 请选择产品 --</option>
                    @foreach($products as $product)
            <option value="{{ $product->id }}"
                                data-price="{{ $product->price }}"
                                data-currency="{{ $product->currency }}">
                            {{ $product->name }}
            </option>
@endforeach
            </select>
        </div>
        <div class="col">
            <input type="number" name="items[${count}][product_price]" class="form-control product-price" placeholder="金额" readonly>
            </div>
            <div class="col">
                <input type="text" name="items[${count}][currency]" class="form-control product-currency" placeholder="币种" readonly>
            </div>
            <div class="col">
                <input type="text" name="items[${count}][quantity]" class="form-control product-quantity" placeholder="数量">
            </div>
            <div class="col-auto">
                <button type="button" class="btn btn-danger btn-remove-item">✖</button>
            </div>
        `;

            container.appendChild(newRow);
            attachProductSelectEvents(newRow);
        });

        // 删除明细
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('btn-remove-item')) {
                e.target.closest('.order-item').remove();
            }
        });
    });
</script>


<!-- 引入 SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.btn-delete-order').forEach(btn => {
            btn.addEventListener('click', function () {
                const orderId = this.getAttribute('data-id');

                Swal.fire({
                    title: '确定删除订单？',
                    text: '删除后将无法恢复该订单记录！',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '是的，删除',
                    cancelButtonText: '取消'
                }).then((result) => {
                    if (result.isConfirmed) {
                        axios.delete(`/orders/${orderId}`)
                            .then(res => {
                                Swal.fire({
                                    title: '已删除！',
                                    text: res.data.message || '订单已成功删除。',
                                    icon: 'success',
                                    confirmButtonText: '确定'
                                }).then(() => {
                                    location.reload();
                                });
                            })
                            .catch(err => {
                                console.error(err);
                                Swal.fire({
                                    title: '删除失败',
                                    text: '请稍后再试。',
                                    icon: 'error',
                                    confirmButtonText: '确定'
                                });
                            });
                    }
                });
            });
        });
    });

    function editOrderStatus(orderId, currentStatus) {
        Swal.fire({
            title: '修改订单状态',
            html: `
            <select id="newStatus" class="swal2-input">
                <option value="pending" ${currentStatus === 'pending' ? 'selected' : ''}>待付款</option>
                <option value="paid" ${currentStatus === 'paid' ? 'selected' : ''}>已付款</option>
                <option value="shipped" ${currentStatus === 'shipped' ? 'selected' : ''}>已发货</option>
            </select>
            <textarea id="remark" class="swal2-textarea" placeholder="备注（可选）"></textarea>
        `,
            focusConfirm: false,
            showCancelButton: true,
            confirmButtonText: '保存',
            cancelButtonText: '取消',
            preConfirm: () => {
                return {
                    status: document.getElementById('newStatus').value,
                    remark: document.getElementById('remark').value
                };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const data = result.value;
                fetch(`/orders/${orderId}/status`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                    .then(res => res.json())
                    .then(res => {
                        if (res.success) {
                            Swal.fire('已更新', '订单状态已修改成功', 'success');
                            // 页面可选刷新或局部更新
                            setTimeout(() => location.reload(), 800);
                        } else {
                            Swal.fire('错误', res.message || '更新失败', 'error');
                        }
                    })
                    .catch(err => Swal.fire('错误', '请求失败，请稍后再试', 'error'));
            }
        });
    }

    document.getElementById('submitOrderBtn').addEventListener('click', function (e) {
        e.preventDefault(); // 阻止表单默认提交

        let form = document.getElementById('createOrderForm');
        let formData = new FormData(form);

        axios.post('/orders', formData)
            .then(res => {
                Swal.fire({
                    title: '✅ 订单创建成功',
                    text: '订单编号：' + res.data.order_id,
                    icon: 'success',
                    confirmButtonText: '返回订单列表',
                }).then(result => {
                    if (result.isConfirmed) {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('createOrderModal'));
                        modal.hide();
                        window.location.href = '/orders';
                    }
                });
            })
            .catch(err => {
                if (err.response && err.response.status === 422) {
                    // 获取验证错误信息
                    let errors = err.response.data.errors;
                    let html = '';
                    for (let key in errors) {
                        errors[key].forEach(msg => {
                            html += msg + '\n'; // 每条错误换行
                        });
                    }

                    // 使用 Swal 弹出
                    Swal.fire({
                        title: '⚠️ 表单验证失败',
                        text: html,
                        icon: 'warning'
                    });
                } else {
                    Swal.fire({
                        title: '❌ 服务器错误',
                        text: '请稍后再试',
                        icon: 'error'
                    });
                }
            });
    });
</script>
@endsection

