<?php

namespace App\Http\Controllers\Cart;

use App\Models\Cart;
use App\Models\User;
use Inertia\Inertia;
use App\Models\Product;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cart\ShowCartRequest;
use App\Http\Requests\Cart\StoreCartRequest;
use Symfony\Component\HttpFoundation\Response;

class CartController extends Controller
{
    public function index() {
        return Inertia::render('ShoppingCart', [
            'availableProducts' => Product::all(),
            'user' => auth()->user()
        ]);
    }

    public function show(ShowCartRequest $request)
    {
        $user = User::findOrFail(Arr::get($request->body, 'userId'));

        $products = $user->cart->products;

        return response()->json($products, Response::HTTP_OK);
    }

    public function store(StoreCartRequest $request)
    {
        $cart = Arr::get($request->body, 'cart');
        $user = User::findOrFail(Arr::get($request->body, 'userId'));

        DB::transaction(function () use ($user, $cart): void {
            if (!$user->cart) {
                Cart::create(['user_id' => $user->id]);
            } else {
                collect($user->cart->products)->each(function ($product) use ($user) {
                    $user->cart->products()->detach($product->id);
                });
            }
    
            collect(json_decode($cart))->each(function ($cartItem) use ($user) {
                $product = Product::find($cartItem->id);
    
                if (!$product) {
                    return;
                }
    
                $user->cart->products()->attach([$product->id => ['quantity' => $cartItem->quantity]]);
            });
        });
        
        return response()->json("OK", Response::HTTP_OK);
    }
}
