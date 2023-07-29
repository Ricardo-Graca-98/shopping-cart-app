<?php

namespace App\Http\Controllers\Cart;

use Inertia\Inertia;
use App\Models\Product;
use App\Http\Controllers\Controller;

class ShoppingCartController extends Controller
{
    public function index() {
        return Inertia::render('ShoppingCart', [
            'availableProducts' => Product::all(),
            'emailRoute' => route('cart.email.send')
        ]);
    }
}
