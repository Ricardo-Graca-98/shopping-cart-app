<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Cart\CartController;
use App\Http\Controllers\Cart\CartEmailController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('cart')->name('cart.')->group(function () {
    Route::post('email/send', [CartEmailController::class, 'send'])->name('email.send');
    Route::post('store', [CartController::class, 'store'])->name('store');
    Route::get('retrieve', [CartController::class, 'show'])->name('show');
});
