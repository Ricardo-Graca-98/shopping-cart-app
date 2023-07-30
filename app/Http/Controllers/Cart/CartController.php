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
    public function index()
    {
        return Inertia::render('ShoppingCart', [
            'availableProducts' => Product::all(),
            'user' => auth()->user()
        ]);
    }

    public function show(Request $request)
    {
        if (!$request->userId) {
            return response()->json('No user ID provided.', Response::HTTP_BAD_REQUEST);
        }

        $user = User::findOrFail($request->userId);

        if (!$user->cart) {
            $products = [];
        } else {
            $products = $user->cart->products;
        }

        return response()->json($products, Response::HTTP_OK);
    }

    public function store(StoreCartRequest $request)
    {
        $cart = Arr::get($request->body, 'cart');
        $user = User::findOrFail(Arr::get($request->body, 'userId'));

        if (is_null($user->cart)) {
            $userCart = Cart::create(['user_id' => $user->id]);
        } else {
            $userCart = $user->cart;
            collect($userCart->products)->each(function ($product) use ($userCart) {
                $userCart->products()->detach($product->id);
            });
        }

        collect(json_decode($cart))->each(function ($cartItem) use ($userCart) {
            $product = Product::find($cartItem->id);

            if (!$product) {
                return;
            }

            $userCart->products()->attach([$product->id => ['quantity' => $cartItem->quantity]]);
        });

        return response()->json("OK", Response::HTTP_OK);
    }
}
