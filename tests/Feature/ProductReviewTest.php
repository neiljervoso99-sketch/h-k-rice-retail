<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_review_a_purchased_product_after_completion_and_admin_can_approve(): void
    {
        Storage::fake('public');
        $customer = User::factory()->create(['role' => 'customer']);
        $admin = User::factory()->create(['role' => 'admin', 'is_admin' => true]);
        $product = Product::query()->create([
            'name' => 'Jasmine Rice',
            'inventory_type' => 'rice',
            'stock_kg' => 50,
            'price_per_kg' => 60,
            'price_confirmed' => true,
            'is_active' => true,
        ]);
        $order = $this->makeOrder($customer, 'completed');
        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'unit_type' => 'kg',
            'quantity' => 5,
            'quantity_kg' => 5,
            'unit_price' => 60,
            'line_total' => 300,
        ]);

        $this->actingAs($customer)
            ->post(route('customer.orders.reviews.store', [$order, $product]), [
                'rating' => 5,
                'review' => 'Good quality.',
                'photo' => UploadedFile::fake()->image('rice-review.jpg'),
            ])
            ->assertRedirect(route('customer.orders.show', $order));

        $review = ProductReview::query()->firstOrFail();
        $this->assertFalse($review->is_approved);
        $this->assertNotNull($review->photo_path);
        Storage::disk('public')->assertExists($review->photo_path);

        $this->actingAs($admin)
            ->get(route('admin.reviews.index'))
            ->assertOk()
            ->assertSee('Good quality.');

        $this->patch(route('admin.reviews.approve', $review))
            ->assertRedirect(route('admin.reviews.index'));

        $this->actingAs($customer)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Jasmine Rice')
            ->assertSee('1 reviews')
            ->assertSee('Customer review photo for Jasmine Rice');
    }

    public function test_customers_cannot_review_pending_orders_or_other_customers_orders(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $owner = User::factory()->create(['role' => 'customer']);
        $product = Product::query()->create([
            'name' => 'Rice',
            'inventory_type' => 'rice',
            'stock_kg' => 20,
            'price_per_kg' => 50,
            'price_confirmed' => true,
            'is_active' => true,
        ]);
        $pendingOrder = $this->makeOrder($customer, 'pending');
        $otherOrder = $this->makeOrder($owner, 'completed');

        foreach ([$pendingOrder, $otherOrder] as $order) {
            OrderItem::query()->create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'unit_type' => 'kg',
                'quantity' => 5,
                'quantity_kg' => 5,
                'unit_price' => 50,
                'line_total' => 250,
            ]);
        }

        $this->actingAs($customer)
            ->post(route('customer.orders.reviews.store', [$pendingOrder, $product]), [
                'rating' => 5,
            ])
            ->assertForbidden();

        $this->post(route('customer.orders.reviews.store', [$otherOrder, $product]), [
            'rating' => 5,
        ])->assertNotFound();
    }

    private function makeOrder(User $customer, string $status): Order
    {
        return Order::query()->create([
            'order_number' => 'HK-' . fake()->unique()->numerify('######'),
            'user_id' => $customer->id,
            'recipient_name' => $customer->name,
            'phone' => '09123456789',
            'status' => $status,
            'payment_status' => 'paid',
            'subtotal' => 300,
            'total' => 300,
            'completed_at' => $status === 'completed' ? now() : null,
        ]);
    }
}