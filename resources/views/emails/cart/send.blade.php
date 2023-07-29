<x-mail::message>
# Your shopping cart is here:

<x-mail::table>
| Item | Price | Quantity | Total
| ------------- |:-------------:|:-------------:|:-------------:|
@foreach($cartItems as $cartItem)
| {{ $cartItem->title }} | £{{ $cartItem->price }} | x{{ $cartItem->quantity }} | £{{ $cartItem->price * $cartItem->quantity }}
@endforeach
</x-mail::table>

## Total £{{$orderTotal}}

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
