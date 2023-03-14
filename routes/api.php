<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OTPController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TransactionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::get('products', [ProductController::class, 'list'])->name('list');

Route::middleware('api')->group(function() {
    Route::post('register', [UserController::class, 'store'])->name('register');
    Route::post('login', [AuthController::class, 'login'])->name('login');
});

Route::prefix('otp')->group(function() {
    Route::post('generate', [OTPController::class, 'create'])->name('otp.generate');
    Route::post('verification', [OTPController::class, 'update'])->name('otp.update');
});
Route::middleware('jwt.verify')->group(function() {
    Route::prefix('transaction')->group(function() {
        Route::post('checkout', [TransactionController::class, 'store'])->name('transaction.store');
        Route::get('detail/{id}', [TransactionController::class, 'show'])->name('transaction.detail');
    });

    Route::middleware('check.role')->prefix('products')->group(function() {
        Route::post('/', [ProductController::class, 'store'])->name('products.store');
        Route::put('/{id}', [ProductController::class, 'update'])->name('products.update');
        Route::patch('/{id}', [ProductController::class, 'update_attribute'])->name('products.update_attribute');
        Route::delete('/{id}', [ProductController::class, 'destroy'])->name('products.destroy');
    });
});
    

Route::middleware('api')->get('/user', function (Request $request) {
    return $request->user();
});

// Route::middleware('auth:sanctum')->group(function(){
//     Route::get('gifts', [ProductController::class, 'list'])->name('list');
//     Route::get('gifts/{id}', [ProductController::class, 'show'])->name('show');
//     Route::post('gifts', [ProductController::class, 'store'])->name('store');
//     Route::put('gifts/{id}', [ProductController::class, 'update'])->name('update');
//     Route::patch('gifts/{id}', [ProductController::class, 'update_attribute'])->name('update_attribute');
//     Route::delete('gifts/{id}', [ProductController::class, 'destroy'])->name('destroy');
//     Route::post('gifts/{id}/rating', [ProductController::class, 'rate_product'])->name('rating');
//     Route::post('gifts/{id}/redeem', [ProductController::class, 'redeem'])->name('redeem');
//     Route::post('gifts/redeem', [ProductController::class, 'multi_redeem'])->name('multi_redeem');
// });
