<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(){
        $products = Product::all();
        return response()->json(array("data"=> $products), 200);
    }

    public function create(Request $request){
        try{
            $this->validate($request, [
                'name' => 'required',
                'price' => 'numeric',
                'inventory' => 'numeric'
            ]);
            $data = Product::query()->create($request->all());
            if($data){
                return response()->json(array("message"=> "Success Create Item"), 201);
            }
        }catch(\Exception $e){
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function show($_id){
        $product = Product::findOrFail($_id);
        return response()->json(array("data"=> $product), 200);
    }

    public function update(Request $request, Product $product){
        try{
            $updated = $product->update($request->all());
            if($updated){
                return response()->json(array("message"=> "Success Update Item"), 200);
            }
        }catch(ModelNotFoundException $e){
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function delete(Product $product){
        try{
            $deleted = $product->delete();
            if($deleted){
                return response()->json(array("message"=> "Success delete Item"), 200);
            }
        }catch(ModelNotFoundException $e){
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}
