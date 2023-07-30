<?php

namespace App\Http\Controllers\Cart;

use App\Mail\SendCart;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\Cart\SendCartEmailRequest;

class CartEmailController extends Controller
{
    public function send(SendCartEmailRequest $request) {
        $body = $request->all();

        $email = Arr::get($body, 'body.email');
        $cart = json_decode(Arr::get($body, 'body.cart'));

        try {
            Mail::to($email)->send(new SendCart($cart));
        } catch (\Exception $e) {
            Log::error("Something went wrong when sending email.", $e->getMessage());

            response()->json($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json($request->all(), Response::HTTP_OK);
    }
}
