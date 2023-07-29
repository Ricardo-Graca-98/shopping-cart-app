<?php

namespace App\Mail;

use Illuminate\Support\Arr;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendCart extends Mailable
{
    use Queueable, SerializesModels;

    protected array $cartItems;

    /**
     * Create a new message instance.
     */
    public function __construct(array $cartItems)
    {
        $this->cartItems = $cartItems;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Here's your cart!",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.cart.send',
            with: [
                'cartItems' => $this->cartItems,
                'orderTotal' => $this->cartTotal()
            ]
        );
    }

    protected function cartTotal(): int
    {
        return collect($this->cartItems)
            ->map(function ($item) {
                return json_decode(json_encode($item), true);
            })
            ->reduce(function (int $carry, array $item) {
                return $carry + (Arr::get($item,'quantity', 1) * Arr::get($item, 'price', 0));
            }, 
        0);
    }
}
