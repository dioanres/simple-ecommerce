<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\OtpModel;
use Illuminate\Http\Request;

class OTPController extends Controller
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
    public function create(Request $request)
    {
        $otp = mt_rand(111111, 999999);
        $expired = Carbon::now()->addMinute(5);
        $email = $request->email;

        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User Not Found.',
            ], 404);
        }

        $otp_model = OtpModel::create([
            'user_id' => $user->id,
            'otp' => $otp,
            'expired_at' => $expired,
        ]);

        if (!$otp_model) {
            return response()->json([
                'success' => false,
                'message' => 'Failed Create OTP.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'otp' => $otp,
            'expired_at' => $expired
        ], 201);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
    public function update(Request $request)
    {
        $input = $request->all();

        try {
            $user = User::where('email', $input['email'])->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User Not Found.',
                ], 404);
            }

            $otp_model = OtpModel::where('user_id', $user->id)
                ->where('otp', $input['otp'])
                ->first();

            if(!$otp_model) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid OTP.',
                ], 404);
            }

            if (Carbon::now()->gt(Carbon::parse($otp_model->expired_at))) {
                return response()->json([
                    'success' => false,
                    'message' => 'OTP Expired.',
                ], 400);
            }

            $user->email_verified_at = Carbon::now();
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'OTP Verified.',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 500);
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
        //
    }
}
