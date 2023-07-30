<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Cart;
use App\Models\User;
use Inertia\Inertia;
use App\Models\Product;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CartControllerTest extends TestCase
{
    // Test for CartController@show with valid user ID
    public function testShowCartWithValidUserId()
    {
        $user = User::factory()->create();
        $cart = Cart::factory()->create(['user_id' => $user->id]);

        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        $cart->products()->attach([$product1->id => ['quantity' => 2]]);
        $cart->products()->attach([$product2->id => ['quantity' => 3]]);

        $this->actingAs($user);

        $response = $this->call('GET', route('cart.show'), ['userId' => $user->id]);
        
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson($cart->products->toArray());
    }

    // Test for CartController@show with missing user ID
    public function testShowCartWithMissingUserId()
    {
        $response = $this->get(route('cart.show'));

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $response->assertExactJson(['No user ID provided.']);
    }

    // Test for CartController@store
    public function testStoreCartWithoutUserCart()
    {
        $user = User::factory()->create();

        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        $cartItems = [
            ['id' => $product1->id, 'quantity' => 2],
            ['id' => $product2->id, 'quantity' => 3],
        ];

        $this->actingAs($user);

        $response = $this->post(route('cart.store'), [
            'body' => [
                'userId' => $user->id,
                'cart' => json_encode($cartItems),
            ]
        ]);

        $response->assertStatus(Response::HTTP_OK);

        $this->assertDatabaseHas('carts', ['user_id' => $user->id]);

        $this->assertDatabaseHas('cart_product', [
            'cart_id' => $user->cart->id,
            'product_id' => $product1->id,
            'quantity' => 2,
        ]);

        $this->assertDatabaseHas('cart_product', [
            'cart_id' => $user->cart->id,
            'product_id' => $product2->id,
            'quantity' => 3,
        ]);
    }

    // Test for CartController@store
    public function testStoreCartWithUserCart()
    {
        $user = User::factory()->create();

        $cart = Cart::factory()->create(['user_id' => $user->id]);

        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        $cartItems = [
            ['id' => $product1->id, 'quantity' => 2],
            ['id' => $product2->id, 'quantity' => 3],
        ];

        $this->actingAs($user);

        $response = $this->post(route('cart.store'), [
            'body' => [
                'userId' => $user->id,
                'cart' => json_encode($cartItems),
            ]
        ]);

        $response->assertStatus(Response::HTTP_OK);

        $this->assertDatabaseHas('carts', ['user_id' => $user->id]);

        $this->assertDatabaseHas('cart_product', [
            'cart_id' => $user->cart->id,
            'product_id' => $product1->id,
            'quantity' => 2,
        ]);

        $this->assertDatabaseHas('cart_product', [
            'cart_id' => $user->cart->id,
            'product_id' => $product2->id,
            'quantity' => 3,
        ]);
    }
}
