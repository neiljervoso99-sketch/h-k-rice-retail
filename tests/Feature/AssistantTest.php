<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Expense;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
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

    public function test_admin_assistant_answers_live_sales_and_order_questions(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
        ]);
        $order = $this->makeOrder($admin, 'HK-SALES-1001');
        $order->update([
            'status' => 'completed',
            'completed_at' => now(),
            'total' => 850,
        ]);

        $this->actingAs($admin)
            ->postJson(route('assistant.message'), ['message' => 'What was our revenue this month?'])
            ->assertOk()
            ->assertJsonPath('message', fn (string $message) => str_contains($message, '₱850.00'))
            ->assertJsonPath('message', fn (string $message) => str_contains($message, '1 orders'));

        $this->postJson(route('assistant.message'), ['message' => 'Show order HK-SALES-1001'])
            ->assertOk()
            ->assertJsonPath('message', fn (string $message) => str_contains($message, 'Completed'))
            ->assertJsonPath('message', fn (string $message) => str_contains($message, '₱850.00'));
    }

    public function test_admin_assistant_answers_product_stock_expense_and_purchase_questions(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
        ]);
        Product::query()->create([
            'name' => 'Jasmine Rice',
            'inventory_type' => 'rice',
            'stock_kg' => 3,
            'low_stock_threshold' => 5,
            'price_per_kg' => 55,
            'price_confirmed' => true,
            'is_active' => true,
        ]);
        Expense::query()->create([
            'category' => 'Utilities',
            'description' => 'Electric bill',
            'amount' => 1250,
            'spent_at' => today(),
        ]);
        $supplier = Supplier::query()->create([
            'name' => 'Test Supplier',
            'is_active' => true,
        ]);
        PurchaseOrder::query()->create([
            'order_number' => 'PO-ASSISTANT-1',
            'supplier_id' => $supplier->id,
            'status' => 'ordered',
        ]);

        $this->actingAs($admin)
            ->postJson(route('assistant.message'), ['message' => 'What is the stock of Jasmine Rice?'])
            ->assertOk()
            ->assertJsonPath('message', fn (string $message) => str_contains($message, 'Jasmine Rice: 3.00 kg'))
            ->assertJsonPath('message', fn (string $message) => str_contains($message, 'LOW STOCK'));

        $this->postJson(route('assistant.message'), ['message' => 'How much did we spend this month?'])
            ->assertOk()
            ->assertJsonPath('message', fn (string $message) => str_contains($message, '₱1,250.00'))
            ->assertJsonPath('message', fn (string $message) => str_contains($message, 'Utilities'));

        $this->postJson(route('assistant.message'), ['message' => 'Show supplier and purchase order status'])
            ->assertOk()
            ->assertJsonPath('message', fn (string $message) => str_contains($message, 'Test Supplier'))
            ->assertJsonPath('message', fn (string $message) => str_contains($message, 'PO-ASSISTANT-1'));
    }

    public function test_admin_assistant_can_report_recent_staff_activity_without_sensitive_details(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
        ]);
        $staff = User::factory()->create(['role' => 'staff', 'name' => 'Store Clerk']);
        ActivityLog::query()->create([
            'user_id' => $staff->id,
            'event' => 'admin.manual-transactions.store',
            'changes' => ['status_code' => 302, 'input' => ['amount' => 30]],
            'created_at' => now(),
        ]);

        $this->actingAs($admin)
            ->postJson(route('assistant.message'), ['message' => 'Show recent staff activity'])
            ->assertOk()
            ->assertJsonPath('message', fn (string $message) => str_contains($message, 'Store Clerk'))
            ->assertJsonPath('message', fn (string $message) => str_contains($message, 'Manual Transactions Store'))
            ->assertJsonPath('message', fn (string $message) => ! str_contains($message, '30'));
    }

    public function test_admin_assistant_reports_pos_transactions_without_customer_contact_data(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
        ]);
        $walkIn = User::factory()->create([
            'role' => 'customer',
            'email' => 'walkin@hk-store.local',
            'name' => 'Walk-in Customer',
            'phone' => '09123456789',
        ]);
        $transaction = $this->makeOrder($walkIn, 'HK-POS-1001');
        $transaction->update([
            'payment_method' => 'cash',
            'total' => 275,
        ]);

        $this->actingAs($admin)
            ->postJson(route('assistant.message'), ['message' => 'Show POS transactions today'])
            ->assertOk()
            ->assertJsonPath('message', fn (string $message) => str_contains($message, '₱275.00'))
            ->assertJsonPath('message', fn (string $message) => str_contains($message, 'HK-POS-1001'))
            ->assertJsonPath('message', fn (string $message) => ! str_contains($message, '09123456789'));
    }

    public function test_admin_assistant_reports_payment_status_and_user_role_counts(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
        ]);
        $customer = User::factory()->create(['role' => 'customer']);
        $order = $this->makeOrder($customer, 'HK-PAYMENT-1001');
        Payment::query()->create([
            'order_id' => $order->id,
            'method' => 'gcash',
            'amount' => 500,
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->postJson(route('assistant.message'), ['message' => 'Show pending payment status today'])
            ->assertOk()
            ->assertJsonPath('message', fn (string $message) => str_contains($message, 'Pending: 1'))
            ->assertJsonPath('message', fn (string $message) => str_contains($message, '₱500.00'));

        $this->postJson(route('assistant.message'), ['message' => 'How many customers and staff?'])
            ->assertOk()
            ->assertJsonPath('message', fn (string $message) => str_contains($message, 'Customers: 1'))
            ->assertJsonPath('message', fn (string $message) => str_contains($message, 'Staff: 0'));
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
