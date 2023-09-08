<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderTest extends TestCase
{
    public function test_index()
    {
        $this->withExceptionHandling();

        $user = User::query()->first();
        Sanctum::actingAs($user);

        $this->get('/api/orders')
            ->assertStatus(200);
    }

    public function test_show()
    {
        $this->withExceptionHandling();

        $user = User::query()->first();
        Sanctum::actingAs($user);

        $order = Order::query()->first();
        $this->get('/api/orders/' . $order->_id)
            ->assertStatus(200);
    }

    public function test_create()
    {
        $this->withExceptionHandling();

        $user = User::query()->first();
        Sanctum::actingAs($user);

        $product1 = Product::query()->first();
        $product2 = Product::query()->latest()->first();
        $count1 = 1;
        $count2 = 2;
        $items = [
            [
                'product_id' => $product1->_id,
                'count' => 1,
            ],
            [
                'product_id' => $product2->_id,
                'count' => 2,
            ]
        ];
        $input = [
            'orderItems' => [
                'upsert' => $items
            ]
        ];
        $response = $this->post('/api/orders', $input);
        $response->assertOk();

        unset($input['orderItems']);
        $input['_id'] = $response->getData()->_id;
        $input['total_price'] = ($product1->price * $count1) + ($product2->price * $count2);
        $input['user_id'] = $user->_id;
        $this->assertDatabaseHas(Order::class, $input);

        $items[0]['order_id'] = $response->getData()->_id;
        $items[1]['order_id'] = $response->getData()->_id;
        $items[0]['unit_price'] = $product1->price;
        $items[1]['unit_price'] = $product2->price;
        $this->assertDatabaseHas(OrderItem::class, $items[0]);
        $this->assertDatabaseHas(OrderItem::class, $items[1]);
    }

    public function test_update()
    {
        $this->withExceptionHandling();

        $user = User::query()->first();
        Sanctum::actingAs($user);

        $order = Order::query()->first();
        $orderItem = $order->orderItems()->first();

        // Add one more to one of products in order
        $newOrderItem = [
            '_id' => $orderItem->_id,
            'order_id' => $order->_id,
            'count' => $orderItem->count + 1
        ];
        $input = [
            'orderItems' => [
                'upsert' => [$newOrderItem]
            ]
        ];
        $productTest = [
            '_id' => $orderItem->product_id,
            'inventory' => $orderItem->product->inventory - 1
        ];
        $orderTest = [
            '_id' => $order->_id,
            'total_price' => $order->total_price + $orderItem->product->price
        ];
        $response = $this->put('/api/orders/' . $order->_id, $input);
        $response->assertOk();
        $this->assertDatabaseHas(OrderItem::class, $newOrderItem);
        $this->assertDatabaseHas(Order::class, $orderTest);
        $this->assertDatabaseHas(Product::class, $productTest);

        // Delete one of products of order
        $input = [
            'orderItems' => [
                'delete' => [$orderItem->_id]
            ]
        ];
        $productTest = [
            '_id' => $orderItem->product_id,
            'inventory' => $orderItem->product->inventory + $orderItem->count
        ];
        $response = $this->put('/api/orders/' . $order->_id, $input);
        $response->assertOk();
        $this->assertSoftDeleted(OrderItem::class, ['_id' => $orderItem->_id]);
        $this->assertDatabaseHas(Product::class, $productTest);
    }

    public function test_cancel()
    {
        $this->withExceptionHandling();

        $user = User::query()->first();
        Sanctum::actingAs($user);

        $order = Order::query()->first();
        $productCounts = [];
        foreach ($order->orderItems as $orderItem) {
            array_push($productCounts, [
                '_id' => $orderItem->product_id,
                'inventory' => $orderItem->product->inventory + $orderItem->count
            ]);
        }
        $orderItemIds = $order->orderItems->pluck('_id');
        $this->put('api/orders/' . $order->_id . '/cancel')
            ->assertOk();
        $this->assertSoftDeleted(Order::class, ['_id' => $order->_id]);
        foreach ($orderItemIds as $_id) {
            $this->assertSoftDeleted(OrderItem::class, ['_id' => $_id]);
        }
        foreach ($productCounts as $productCount) {
            $this->assertDatabaseHas(Product::class, $productCount);
        }
    }
}
