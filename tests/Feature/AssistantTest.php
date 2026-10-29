<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssistantTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_faq_returns_store_checkout_information(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_admin' => true]);

        $this->actingAs($admin)->postJson(route('assistant.message'), [
            'message' => 'What payment methods can I use?',
        ])
            ->assertOk()
            ->assertJsonPath('products', [])
            ->assertJsonPath('message', fn (string $message) => str_contains($message, 'GCash'));
    }

    public function test_ordering_faq_explains_the_checkout_steps(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_admin' => true]);

        $this->actingAs($admin)->postJson(route('assistant.message'), [
            'message' => 'How can I place an order?',
        ])
            ->assertOk()
            ->assertJsonPath('message', fn (string $message) => str_contains($message, 'add your items to the cart'))
            ->assertJsonPath('message', fn (string $message) => str_contains($message, 'remains pending'));
    }

    public function test_recommendations_only_return_active_products_with_stock(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_admin' => true]);
        $available = Product::query()->create([
            'name' => 'Brown Rice',
            'rice_type' => 'Brown Rice',
            'inventory_type' => 'rice',
            'stock_kg' => 35,
            'low_stock_threshold' => 5,
            'price_per_kg' => 62,
            'price_confirmed' => true,
            'is_active' => true,
        ]);

        Product::query()->create([
            'name' => 'Brown Rice Out of Stock',
            'rice_type' => 'Brown Rice',
            'inventory_type' => 'rice',
            'stock_kg' => 0,
            'is_active' => true,
        ]);

        Product::query()->create([
            'name' => 'Inactive Brown Rice',
            'rice_type' => 'Brown Rice',
            'inventory_type' => 'rice',
            'stock_kg' => 20,
            'is_active' => false,
        ]);

        $this->actingAs($admin)->postJson(route('assistant.message'), [
            'message' => 'Recommend brown rice',
        ])
            ->assertOk()
            ->assertJsonPath('products.0.name', $available->name)
            ->assertJsonPath('products.0.price', '₱62.00 per kg')
            ->assertJsonMissing(['name' => 'Brown Rice Out of Stock'])
            ->assertJsonMissing(['name' => 'Inactive Brown Rice']);
    }

    public function test_affordable_recommendations_prioritize_lower_confirmed_prices(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_admin' => true]);
        Product::query()->create([
            'name' => 'Premium Rice',
            'inventory_type' => 'rice',
            'stock_kg' => 20,
            'price_per_kg' => 65,
            'price_confirmed' => true,
            'is_active' => true,
        ]);
        Product::query()->create([
            'name' => 'Budget Rice',
            'inventory_type' => 'rice',
            'stock_kg' => 20,
            'price_per_kg' => 48,
            'price_confirmed' => true,
            'is_active' => true,
        ]);

        $this->actingAs($admin)->postJson(route('assistant.message'), [
            'message' => 'Recommend affordable rice',
        ])
            ->assertOk()
            ->assertJsonPath('products.0.name', 'Budget Rice');
    }

    public function test_customers_and_guests_cannot_use_the_assistant_endpoint(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $otherCustomer = User::factory()->create(['role' => 'customer']);

        $ownOrder = $this->makeOrder($customer, 'HK-OWN-1001');
        $this->makeOrder($otherCustomer, 'HK-PRIVATE-2002');

        $this->postJson(route('assistant.message'), ['message' => 'Track my order'])
            ->assertUnauthorized();

        $this->actingAs($customer)
            ->postJson(route('assistant.message'), ['message' => 'Track my order'])
            ->assertForbidden();

        $this->postJson(route('assistant.message'), ['message' => 'Show inventory summary'])
            ->assertForbidden();
    }

    public function test_admin_can_get_an_aggregate_store_summary(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
        ]);

        Product::query()->create([
            'name' => 'Low Stock Rice',
            'inventory_type' => 'rice',
            'stock_kg' => 2,
            'low_stock_threshold' => 5,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->postJson(route('assistant.message'), ['message' => 'Show inventory summary'])
            ->assertOk()
            ->assertJsonPath('message', fn (string $message) => str_contains($message, '1 low-stock products'))
            ->assertJsonPath('message', fn (string $message) => str_contains($message, 'Low Stock Rice'));
    }

    public function test_assistant_widget_is_available_to_admins_only(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('Ask assistant');

        $customer = User::factory()->create(['role' => 'customer']);
        $this->actingAs($customer)
            ->get(route('home'))
            ->assertOk()
            ->assertDontSee('Ask assistant');

        $staff = User::factory()->create(['role' => 'staff']);
        $this->actingAs($staff)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertDontSee('Ask assistant');

        $admin = User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
        ]);
        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Ask assistant');

        $rider = User::factory()->create(['role' => 'rider']);
        $this->actingAs($rider)
            ->get(route('rider.dashboard'))
            ->assertOk()
            ->assertDontSee('Ask assistant');
    }

    private function makeOrder(User $user, string $number): Order
    {
        return Order::query()->create([
            'order_number' => $number,
            'user_id' => $user->id,
            'recipient_name' => $user->name,
            'phone' => '09123456789',
            'status' => 'pending',
            'payment_status' => 'pending',
            'subtotal' => 120,
            'total' => 120,
        ]);
    }
}