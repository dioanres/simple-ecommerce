<?php

namespace App\Http\Controllers;

use App\Models\User;
use Tymon\JWTAuth\JWTAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login_sanctum(Request $request)
    {
        $validator = \Validator::make($request->all(),[
            'email' => 'required',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return responder()->success($validator->errors())->respond(205);
        }
        
        $auth = Auth::attempt($request->all());

        if (Auth::check()) {
            $user = Auth::user();

            $token = $user->createToken('token-api');
            $data = [
                'token' => $token->plainTextToken
            ];

            return responder()->success($data)->respond(200);
        }

        $data = [
            'message' => 'your email or password incorrect.'
        ];

        return responder()->success($data)->respond(201);
    }

    public function login(Request $request)
    {
        $validator = \Validator::make($request->all(),[
            'email' => 'required',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return responder()->success($validator->errors())->respond(205);
        }
        
        // verify user + token
        //Crean token
        try {
            $credentials = $request->all();

            if (! $token = auth()->attempt($credentials)) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $user = User::where('email', $request->email)->first();

            if (!$user->email_verified_at) {
                return response()->json([
                	'success' => false,
                	'message' => 'User Not Verified.',
                ], 400);
            }
        } catch (JWTException $e) {

    	return $credentials;
            return response()->json([
                	'success' => false,
                	'message' => 'Could not create token.',
                ], 500);
        }

        return response()->json([
            'success' => true,
            'token' => $token,
        ]);
    }
    
}
