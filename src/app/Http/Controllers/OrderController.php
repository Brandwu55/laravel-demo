<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;

use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    /*
        取得訂單列表（含簡單篩選）
    */
    public function index(Request $request)
    {
        $query = Order::with(['items.product','user']); // 预加载明细

        $users = \App\Models\User::all();

        $products = Product::all();

        // 简单筛选条件（根据需要可加更多）
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('order_number')) {
            $query->where('order_number', $request->order_number);
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('created_at', [$request->date_from, $request->date_to]);
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('orders.index', compact('orders','users', 'products'));
    }

    public function create()
    {
        return view('orders.create');
    }

    /*
     * 提交訂單保存
     */
    public function store(Request $request)
    {
        $rules = [
            'user_id' => 'required|exists:users,id',
            'payment_method' => 'required|string|in:wechat,alipay,paypal',
            'customer_phone' => 'required|string|regex:/^(09\d{8})$/',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1'
        ];

        $messages = [
            'user_id.required' => '请选择用户',
            'customer_phone.required' => '请输入手机号',
            'customer_phone.regex' => '手机号格式不正确（台湾为09开头10位）',
            'payment_method.required' => '请选择支付方式',
            'payment_method.in' => '支付方式不被支持',
            'items.required' => '请至少选择一个商品',
            'items.*.product_id.required' => '请选择商品',
            'items.*.quantity.required' => '请输入数量',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422, [], JSON_UNESCAPED_UNICODE);
        }

        //事务保存订单
        try {
            DB::beginTransaction();

            // 创建订单主表记录
            $order = Order::create([
                'order_number' => 'ORD-' . now()->timestamp,
                'user_id' => $request->input('user_id'),
                'payment_method' => $request->input('payment_method'),
                'customer_phone' => $request->input('customer_phone'),
                'customer_address' => $request->input('customer_address'),
                'status' => 'pending',
                'total_amount' => 0, // 先设为 0，后面计算
            ]);

            $totalAmount = 0;

            // 循环创建订单明细
            foreach ($request->input('items') as $item) {
                $productId = $item['product_id'];
                $quantity = $item['quantity'];
                $currency = $item['currency'];

                // 查询产品价格（假设 product 表有 price 字段）
                $product = DB::table('products')->find($productId);
                if (!$product) {
                    throw new \Exception('产品不存在');
                }

                $subtotal = $product->price * $quantity;
                $totalAmount += $subtotal;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $productId,
                    'currency' => $currency,
                    'quantity' => $quantity,
                    'price' => $product->price,
                ]);
            }

            // 更新订单总价
            $order->update(['total_amount' => $totalAmount]);

            DB::commit();

            return response()->json([
                'message' => '订单创建成功',
                'order_id' => $order->id,
            ], 200, [], JSON_UNESCAPED_UNICODE);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => '订单创建失败',
                'error' => $e->getMessage(),
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }

    }

    /*
    * 刪除指定訂單及其關聯明細。
    *
    * 此方法會先刪除訂單的所有明細資料，再刪除訂單本身，
    * 使用資料庫事務確保操作的原子性。如果刪除過程中發生例外，
    * 會回滾事務並返回錯誤訊息。
    *
    * @param int $id 訂單ID
    * @return \Illuminate\Http\JsonResponse 返回 JSON 格式的操作結果
    */
    public function destroy($id)
    {
        $order = Order::with('items')->findOrFail($id);

        DB::beginTransaction();
        try {
            // 删除明细
            $order->items()->delete();
            // 删除主表
            $order->delete();

            DB::commit();
            return response()->json(['message' => '订单已删除'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => '删除失败', 'error' => $e->getMessage()], 500);
        }
    }

    /*
     * 更新指定訂單的狀態及備註。
     *
     * 該方法會驗證請求中的 `status` 與 `remark` 字段，並更新對應訂單資料。
     * 若更新過程中發生例外，將返回錯誤訊息。
     *
     * 可用的狀態值：
     * - pending : 待處理
     * - paid    : 已付款
     * - shipped : 已出貨
     *
     * @param \Illuminate\Http\Request $request 請求對象，需包含：
     *      - status (string) 訂單狀態，必填，值為 pending、paid 或 shipped
     *      - remark (string|null) 備註，可選，最大長度 255
     * @param \App\Models\Order $order 要更新的訂單模型實例
     *
     * @return \Illuminate\Http\JsonResponse 返回 JSON 格式的操作結果
     */
    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|string|in:pending,paid,shipped',
            'remark' => 'nullable|string|max:255',
        ]);

        try {
            $order->status = $request->status;
            $order->remarks = $request->remarks;
            $order->save();
        } catch (\Exception $e) {
            return response()->json(['message' => '更新失败', 'error' => $e->getMessage()], 500);
        }

        return response()->json(['success' => true]);
    }
}

