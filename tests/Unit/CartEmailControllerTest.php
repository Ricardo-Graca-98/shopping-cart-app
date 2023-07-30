<?php

use Tests\TestCase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * CartEmailControllerTest
 * 
 * @group cart
 */
class CartEmailControllerTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        Artisan::call('migrate'); // Run migrations before each test
    }

    // Test for CartEmailController@send with invalid request
    public function testSendCartEmailWithInvalidRequest()
    {
        $response = $this->post(route('cart.email.send'), [
            // Missing 'body' parameter
        ], [
            'accept' => 'application/json'
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    // Test for CartEmailController@send with email sending failure
    public function testSendCartEmailWithSendingFailure()
    {
        // Replace with actual data for the cart and email
        $cartData = [
            ['id' => 1, 'name' => 'Product 1', 'quantity' => 2],
            ['id' => 2, 'name' => 'Product 2', 'quantity' => 1],
        ];
        $email = 'test@example.com';

        // Mock the Mail facade to throw an exception when sending email
        Mail::shouldReceive('to')->with($email)->once()->andThrow(new \Exception('Mail sending failed.'));

        // Mock the Log facade to prevent actual logging
        Log::shouldReceive('error')->once();

        // Expecting an exception to be thrown
        $this->expectException(\Exception::class);

        $response = $this->post(route('cart.email.send'), [
            'body' => [
                'email' => $email,
                'cart' => json_encode($cartData),
            ],
        ]);

        // Assert that the email was not sent successfully
        Mail::assertNotSent(SendCart::class);

        // Ensure that the response has a 500 status code
        $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
