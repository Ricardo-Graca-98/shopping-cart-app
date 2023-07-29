<?php

namespace App\Http\Controllers\Cart;

use App\Mail\SendCart;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\Cart\SendCartEmailRequest;

class CartEmailController extends Controller
{
    public function send(SendCartEmailRequest $request) {
        $body = $request->all();

        $email = Arr::get($body, 'body.email');
        $cart = json_decode(Arr::get($body, 'body.cart'));

        Mail::to($email)->send(new SendCart($cart));

        return response()->json($request->all(), 200);
    }
}
