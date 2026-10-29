<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductBundle;
use App\Models\ProductSellingUnit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BundlePacksTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_and_customer_can_add_a_bundle_without_replacing_cart(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_admin' => true]);
        $customer = User::factory()->create(['role' => 'customer']);
        $rice = Product::query()->create([
            'name' => 'Jasmine Rice',
            'inventory_type' => 'rice',
            'stock_kg' => 80,
            'price_per_kg' => 60,
            'price_confirmed' => true,
            'is_active' => true,
        ]);
        $eggs = Product::query()->create([
            'name' => 'Eggs',
            'inventory_type' => 'generic',
            'stock_unit' => 'piece',
            'base_unit' => 'piece',
            'stock_base_quantity' => 30,
            'price_confirmed' => true,
            'is_active' => true,
        ]);
        ProductSellingUnit::query()->create([
            'product_id' => $eggs->id,
            'unit_name' => 'piece',
            'conversion_factor' => 1,
            'selling_price' => 10,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.bundles.store'), [
                'name' => 'Rice and Eggs',
                'description' => 'Everyday essentials',
                'items' => [
                    ['product_id' => $rice->id, 'unit' => 'kg', 'quantity' => 5],
                    ['product_id' => $eggs->id, 'unit' => 'piece', 'quantity' => 6],
                ],
            ])
            ->assertRedirect(route('admin.bundles.index'));

        $bundle = ProductBundle::query()->firstOrFail();
        $this->assertSame(2, $bundle->items()->count());

        $this->actingAs($customer)
            ->withSession(['cart' => ['999' => ['unit' => 'kg', 'quantity' => 5]]])
            ->post(route('bundles.add-to-cart', $bundle))
            ->assertRedirect(route('cart.index'));

        $cart = session('cart');
        $this->assertSame(5.0, (float) $cart['999']['quantity']);
        $this->assertSame(5.0, (float) $cart[$rice->id]['quantity']);
        $this->assertSame(6.0, (float) $cart[$eggs->id]['quantity']);
    }

    public function test_bundle_is_added_atomically_when_any_item_has_insufficient_stock(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $available = Product::query()->create([
            'name' => 'Available Rice', 'inventory_type' => 'rice', 'stock_kg' => 30,
            'price_per_kg' => 50, 'price_confirmed' => true, 'is_active' => true,
        ]);
        $lowStock = Product::query()->create([
            'name' => 'Low Stock Rice', 'inventory_type' => 'rice', 'stock_kg' => 2,
            'price_per_kg' => 50, 'price_confirmed' => true, 'is_active' => true,
        ]);
        $bundle = ProductBundle::query()->create(['name' => 'Two Rice Pack', 'is_active' => true]);
        $bundle->items()->createMany([
            ['product_id' => $available->id, 'unit' => 'kg', 'quantity' => 5],
            ['product_id' => $lowStock->id, 'unit' => 'kg', 'quantity' => 5],
        ]);

        $this->actingAs($customer)
            ->post(route('bundles.add-to-cart', $bundle))
            ->assertSessionHasErrors('cart');

        $this->assertSame([], session('cart', []));
    }
}