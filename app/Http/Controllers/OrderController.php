<?php

namespace App\Http\Controllers;
use App\Models\Order;
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
        return response()->json(array("data"=> $order), 200);
    }

    public function show(Order $order){
        try{
            return response()->json(array("data"=> $order->load(['user', 'orderItems'])), 200);
        }catch(ModelNotFoundException $e){
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function create(Request $request){
        try{
            //
        }catch(\Exception $e){
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function delete(Product $product){
        try{
            //
        }catch(ModelNotFoundException $e){
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}
