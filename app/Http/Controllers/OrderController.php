<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $order = Order::query()
            ->with('orderItems')
            ->get();
        return response()->json(array("data" => $order), 200);
    }

    public function show(Order $order)
    {
        try {
            return response()->json(array("data" => $order->load(['user', 'orderItems'])), 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function create(Request $request)
    {
        try {
            $this->validate($request, [
                'orderItems' => 'required',
            ]);
            $session = DB::getMongoClient()->startSession();
            $session->startTransaction();
            try {
                $orderItems = [];
                $total_price = 0;
                foreach ($request->orderItems['upsert'] as $orderItem) {
                    $product = Product::query()->findOrFail($orderItem['product_id']);
                    if ($product->inventory < $orderItem['count']) {
                        return response()->json(['status' => 'error', 'message' => 'Insufficient Inventory'], 400);
                    }
                    $product->update(['inventory' => $product->inventory - $orderItem['count']]);
                    $total_price += $product->price * $orderItem['count'];
                    $orderItems[] = [
                        'product_id' => $product->id,
                        'unit_price' => $product->price,
                        'count' => $orderItem['count'],
                    ];
                }

                $order = Order::query()->create([
                    'user_id' => auth()->user()->_id,
                    'total_price' => $total_price,
                ]);

                foreach ($orderItems as $orderItem) {
                    $order->orderItems()->create($orderItem);
                }

                if ($order) {
                    return response()->json($order->load(['user', 'orderItems']), 200);
                }
                $session->commitTransaction();
            } catch (\Exception $e) {
                $session->abortTransaction();
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }

    public function update(Request $request, Order $order)
    {
        try {
            $totalPrice = $order->total_price;
            if (isset($request->orderItems['delete'])) {
                foreach ($request->orderItems['delete'] as $deleteId) {
                    $orderItem = OrderItem::query()->findOrFail($deleteId);
                    $totalPrice -= $orderItem->unit_price * $orderItem->count;
                    $orderItem->product->update([
                        'inventory' => $orderItem->product->inventory + $orderItem->count
                    ]);
                    $orderItem->delete();
                }
            }
            if (isset($request->orderItems['upsert'])) {
                foreach ($request->orderItems['upsert'] as $orderItem) {
                    if (isset($orderItem['_id'])) {
                        $item = OrderItem::query()->findOrFail($orderItem['_id']);
                        $totalPrice += ($orderItem['count'] - $item->count) * $item->product->price;
                        $item->product->update(['inventory' => $item->product->inventory - ($orderItem['count'] - $item->count)]);
                        $item->update([
                            'count' => $orderItem['count'],
                        ]);
                    } else {
                        $product = Product::query()->findOrFail($orderItem['product_id']);
                        if ($product->inventory < $orderItem['count']) {
                            return response()->json(['status' => 'error', 'message' => 'Insufficient Inventory'], 400);
                        }
                        $product->update(['inventory' => $product->inventory - $orderItem['count']]);
                        $totalPrice += $product->price * $orderItem['count'];
                        $order->orderItems()->create([
                            'product_id' => $product->id,
                            'unit_price' => $product->price,
                            'count' => $orderItem['count'],
                        ]);
                    }
                }
            }
            $updated = $order->update([
                'total_price' => $totalPrice
            ]);
            if ($updated) {
                return response()->json($order->load(['user', 'orderItems']), 200);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }

    public function cancel(Order $order)
    {
        try {
            foreach ($order->orderItems as $orderItem) {
                $orderItem->product->update(['inventory' => $orderItem->product->inventory + $orderItem->count]);
                $orderItem->delete();
            }
            $deleted = $order->delete();
            if ($deleted) {
                return response()->json(array("message" => "Success delete Item"), 200);
            }
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }
}
