<?php

namespace Tests\Feature\Admin;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductVariantsTest extends TestCase
{
    use RefreshDatabase;

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
}