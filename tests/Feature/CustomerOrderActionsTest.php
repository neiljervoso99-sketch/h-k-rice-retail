<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerOrderActionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_reorder_available_items_into_existing_cart(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $product = Product::query()->create([
            'name' => 'Jasmine Rice',
            'inventory_type' => 'rice',
            'stock_kg' => 80,
            'price_per_kg' => 62,
            'price_confirmed' => true,
            'is_active' => true,
        ]);
        $order = $this->makeOrder($customer);
        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'unit_type' => 'kg',
            'quantity' => 5,
            'quantity_kg' => 5,
            'unit_price' => 62,
            'line_total' => 310,
        ]);

        $this->actingAs($customer)
            ->withSession(['cart' => ['999' => ['unit' => 'kg', 'quantity' => 5]]])
            ->post(route('customer.orders.reorder', $order))
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('cart_success');

        $cart = session('cart');
        $this->assertSame(5.0, (float) $cart['999']['quantity']);
        $this->assertSame(5.0, (float) $cart[$product->id]['quantity']);
    }

    public function test_customer_cannot_reorder_another_users_order(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $owner = User::factory()->create(['role' => 'customer']);
        $order = $this->makeOrder($owner);

        $this->actingAs($customer)
            ->post(route('customer.orders.reorder', $order))
            ->assertNotFound();
    }

    private function makeOrder(User $customer): Order
    {
        return Order::query()->create([
            'order_number' => 'HK-' . fake()->unique()->numerify('######'),
            'user_id' => $customer->id,
            'recipient_name' => $customer->name,
            'phone' => '09123456789',
            'status' => 'completed',
            'payment_status' => 'paid',
            'subtotal' => 310,
            'total' => 310,
        ]);
    }
}