<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductVariantsTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_creation_form_uses_a_single_base_selling_unit_by_default(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_admin' => true]);

        $this->actingAs($admin)
            ->get(route('admin.products.create'))
            ->assertOk()
            ->assertSee('Product Details')
            ->assertSee('Initial Stock')
            ->assertSee('Selling Price per')
            ->assertSee('Add another selling unit (optional)')
            ->assertSee('More settings (optional)');

        $this->post(route('admin.products.store'), [
            'name' => 'Simple Grocery',
            'category' => 'Groceries',
            'base_unit' => 'piece',
            'initial_stock_quantity' => 8,
            'selling_units' => [[
                'unit_name' => 'piece',
                'conversion_factor' => 1,
                'selling_price' => 12,
                'cost_price' => 10,
            ]],
            'low_stock_threshold' => 5,
            'is_active' => 1,
        ])->assertRedirect(route('admin.products.index'));

        $product = Product::where('name', 'Simple Grocery')->firstOrFail();
        $this->assertSame('generic', $product->inventory_type);
        $this->assertSame('8.000', $product->stock_base_quantity);
        $this->assertSame(1, $product->sellingUnits()->count());
    }

    public function test_admin_can_save_size_and_color_variants_for_a_separately_stocked_product(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_admin' => true]);
        $product = Product::query()->create([
            'name' => 'Water Bottle',
            'inventory_type' => 'generic',
            'stock_unit' => 'piece',
            'base_unit' => 'piece',
            'stock_base_quantity' => 12,
            'stock_quantity' => 12,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.products.update', $product), [
                '_product_id' => $product->id,
                'name' => 'Water Bottle',
                'variant_size' => '750 ml',
                'variant_color' => 'Blue',
            ])
            ->assertRedirect(route('admin.products.index'));

        $this->assertSame('750 ml', $product->fresh()->variant_size);
        $this->assertSame('Blue', $product->fresh()->variant_color);
    }

    public function test_removing_a_product_returns_to_the_products_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_admin' => true]);
        $product = Product::query()->create([
            'name' => 'Temporary Product',
            'inventory_type' => 'generic',
            'stock_unit' => 'piece',
            'base_unit' => 'piece',
            'stock_base_quantity' => 0,
            'stock_quantity' => 0,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.products.destroy', $product))
            ->assertRedirect(route('admin.products.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_removing_a_product_with_order_history_deletes_product_and_preserves_order_snapshot(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_admin' => true]);
        $customer = User::factory()->create(['role' => 'customer']);
        $product = Product::query()->create([
            'name' => 'Purchased Product',
            'inventory_type' => 'generic',
            'stock_unit' => 'piece',
            'base_unit' => 'piece',
            'stock_base_quantity' => 3,
            'stock_quantity' => 3,
            'is_active' => true,
        ]);
        $order = Order::query()->create([
            'order_number' => 'HK-REMOVE-HISTORY-1',
            'user_id' => $customer->id,
            'recipient_name' => $customer->name,
            'phone' => '09123456789',
            'status' => 'completed',
            'payment_status' => 'paid',
            'subtotal' => 10,
            'total' => 10,
        ]);
        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'unit_type' => 'piece',
            'quantity' => 1,
            'quantity_kg' => 1,
            'unit_price' => 10,
            'line_total' => 10,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.products.destroy', $product))
            ->assertRedirect(route('admin.products.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => null,
            'product_name' => 'Purchased Product',
        ]);
    }
}
