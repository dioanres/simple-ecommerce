<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\TransactionDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
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
        $validator = \Validator::make($input, [
            'desc' => 'required',
            'total_amount' => 'required',
            'payment_method' => 'required',
            'courier_method' => 'required',
            'transaction_details' => 'required'
        ]);

        if ($validator->fails()) {
            return responder()->success($validator->errors())->respond(422);
        }

        DB::beginTransaction();

        try {
            $input['user_id'] = Auth::user()->id;

            $transaction = Transaction::create($input);

            $this->sync_detail_transaction($transaction, $input['transaction_details']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Success Checkout.',
                'data' => $transaction,
            ], 200);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed Checkout.',
            ], 500);
            throw $th;
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
        $transaction = Transaction::with('transaction_details')->where('id', $id)->first();

        if ($transaction) {
            return response()->json([
                'success' => true,
                'message' => 'Success',
                'data' => $transaction,
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Not Found',
            'data' => $transaction,
        ], 404);
        
    }

    public function sync_detail_transaction($transaction, $details)
    {
        try {
            $exist = TransactionDetail::where('transaction_id', $transaction->id)->delete();

            foreach ($details as $key => $detail) {
                $product = Product::where('id', $detail['product_id'])->lockForUpdate()->first();
                $product->stock = $product->stock - $detail['qty'];
                $product->save();

                $detail['transaction_id'] = $transaction->id;

                TransactionDetail::create($detail);
            }

            return true;
        } catch (\Throwable $th) {
            throw $th;
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
