<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\ProductRating;
use App\Models\ProductRedeem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Transformers\ProductTransformer;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    public function list(Request $request)
    {
        $size = 10;
        $input = $request->all();
        if ($request->size) {
            $size = $request->size;
        }
        $data = Product::paginate($size);
        $rows = (new ProductTransformer())->collection_transform($data->items());
        $count = $data->total();

        $response = [
            'total' => $count,
            'rows'  => $rows
        ];
        return responder()->success($response)->respond(200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();

        $validator = \Validator::make($input,[
            'name' => 'required',
            'desc' => 'required',
            'stock' => 'required',
            'price' => 'nullable'
        ]);

        if ($validator->fails()) {
            return responder()->success($validator->errors())->respond(422);
        }

        try {
            $product = Product::create([
                'name' => $input['name'],
                'desc' => $input['desc'],
                'stock' => $input['stock'],
                'price' => $input['price']
            ]);

            return responder()->success($product)->respond(200);
        } catch (\Throwable $th) {
            return responder()->error(500, $th->getMessage())->respond(500);
        }
        
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $product = Product::find($id);
            if ($product) {
                $response = (new ProductTransformer())->transform($product);
    
                return responder()->success($response)->respond(200);
            }

            return responder()->success(['Not Found'])->respond(200);
        } catch (\Throwable $th) {
            return responder()->error(500, $th->getMessage())->respond(500);
        }
        
        
    }

    public function redeem(Request $request, $id)
    {
        $input = $request->all();

        DB::beginTransaction();
        try {
            $product = Product::find($id);
            $user = Auth::user();

            if ($product->stock < 1 ) {
                return responder()->success(['message' => 'Product is Empty'])->respond(205);
            }

            if ($product->stock < $input['qty']) {
                return responder()->success(['message' => 'Insufficient Stock'])->respond(205);
            }

            $total = $input['qty'] * $product->gift;
            $redeem = ProductRedeem::create([
                'user_id' => $user->id,
                'product_id' => $id,
                'qty' => $input['qty'],
                'points' => $product->gift,
                'total_points' => $total,
            ]);

            //pengurangan point user
            $user->points = $user->points - $total;
            $user->save();

            //pengurangan stock product
            $product->stock = $product->stock - $input['qty'];
            $product->save();

            DB::commit();

            return responder()->success(['message' => 'Success'])->respond(200);
        } catch (\Throwable $th) {
            DB::rollback();
            return responder()->error(500, $th->getMessage())->respond(500);
        }
    }

    public function multi_redeem(Request $request)
    {
        $input = $request->all();
        $user = Auth::user();

        DB::beginTransaction();
        try {

            foreach ($input as $key => $val) {
                $product = Product::find($val['product_id']);

                if ($product->stock < 1 ) {
                    return responder()->success(['message' => 'Product is Empty'])->respond(205);
                }
    
                if ($product->stock < $val['qty']) {
                    return responder()->success(['message' => 'Insufficient Stock'])->respond(205);
                }
    
                $total = $val['qty'] * $product->gift;
                $redeem = ProductRedeem::create([
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                    'qty' => $val['qty'],
                    'points' => $product->gift,
                    'total_points' => $total,
                ]);
    
                //pengurangan point user
                $user->points = $user->points - $total;
                $user->save();
    
                //pengurangan stock product
                $product->stock = $product->stock - $val['qty'];
                $product->save();  
            }

            DB::commit();
    
            return responder()->success(['message' => 'Success'])->respond(200);
            
        } catch (\Throwable $th) {
            DB::rollback();
            return responder()->error(500, $th->getMessage())->respond(500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $input = $request->all();
        try {
            $product = Product::find($id) or abort(404);

            $product->name = $input['name'];
            $product->desc = $input['desc'];
            $product->stock = $input['stock'];
            $product->price = $input['price'];

            $product->save();

            return responder()->success($product)->respond(200);
        } catch (\Throwable $th) {
            return responder()->error(500, $th->getMessage())->respond(500);
        }
       
    }

    public function update_attribute(Request $request, $id)
    {
        $input = $request->all();
        try {
            $product = Product::find($id) or abort(404);
            $product->fill($input);
            $product->save();

            return responder()->success($product)->respond(200);
        } catch (\Throwable $th) {
            return responder()->error(500, $th->getMessage())->respond(500);
        }
       
    }

    public function rate_product(Request $request, $id)
    {
        $input = $request->all();

        $validator = \Validator::make($input,[
            'product_id' => 'nullable',
            'rating' => 'required|numeric|max:5',
            'comment' => 'nullable',
        ]);

        if ($validator->fails()) {
            return responder()->success($validator->errors())->respond(205);
        }

        try {
            $product = Product::find($id) or abort(404);
            
            $rating = ProductRating::create([
                'product_id' => $product->id,
                'rating' => $input['rating'],
                'comment' => $input['comment'],
            ]);

            return responder()->success(['message' => 'Success'])->respond(200);
        } catch (\Throwable $th) {
            return responder()->error(500, $th->getMessage())->respond(500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $product = Product::find($id) or abort(404);
            $product->delete();

            return responder()->success(['message' => 'Success'])->respond(200);
        } catch (\Throwable $th) {
            return responder()->error(500, $th->getMessage())->respond(500);
        }
        
    }
}
