<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductTest extends TestCase
{
    public function test_index()
    {
        $this->withExceptionHandling();

        $user = User::query()->first();
        Sanctum::actingAs($user);

        $this->get('/api/products')
            ->assertStatus(200)
            ->assertJsonStructure(
                [
                    'data' => [
                        '*' => [
                            'name',
                            'count',
                            'price'
                        ],
                    ],
                ]
            );
    }

    public function test_show()
    {
        $this->withExceptionHandling();

        $user = User::query()->first();
        Sanctum::actingAs($user);

        $product = Product::query()->first();
        $this->get('/api/products/' . $product->_id)
            ->assertStatus(200);
    }

    public function test_create()
    {
        $this->withExceptionHandling();

        $user = User::query()->first();
        Sanctum::actingAs($user);

        $input = [
            'name' => 'productTest',
            'inventory' => 100,
            'price' => 1000,
        ];
        $response = $this->post('/api/products', $input);
        $input['_id'] = $response->getData()->_id;
        $response->assertOk();
        $this->assertDatabaseHas(Product::class, $input);
    }

    public function test_update()
    {
        $this->withExceptionHandling();

        $user = User::query()->first();
        Sanctum::actingAs($user);

        $product = Product::query()->first();
        $input = [
            '_id' => $product->_id,
            'name' => 'newProduct',
        ];
        $response = $this->put('/api/products/' . $product->_id, $input);
        $response->assertOk();
    }

    public function test_delete()
    {
        $this->withExceptionHandling();

        $user = User::query()->first();
        Sanctum::actingAs($user);

        $product = Product::query()->first();
        $this->delete('api/products/'. $product->_id)
            ->assertOk();
    }
}
