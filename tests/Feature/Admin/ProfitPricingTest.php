<?php

namespace Tests\Feature\Admin;

use App\Models\Product;
use App\Models\ProductSellingUnit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfitPricingTest extends TestCase
{
    use RefreshDatabase;

    public function test_profit_pricing_shows_active_selling_units_for_generic_products(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
        ]);

        $product = Product::create([
            'name' => 'New Egg Product',
            'inventory_type' => 'generic',
            'base_unit' => 'piece',
            'stock_unit' => 'piece',
            'profit_percentage' => 15,
            'is_active' => true,
        ]);

        ProductSellingUnit::create([
            'product_id' => $product->id,
            'unit_name' => 'piece',
            'conversion_factor' => 1,
            'selling_price' => 12,
            'cost_price' => 10,
            'is_active' => true,
        ]);

        ProductSellingUnit::create([
            'product_id' => $product->id,
            'unit_name' => 'box',
            'conversion_factor' => 12,
            'selling_price' => 120,
            'cost_price' => 100,
            'is_active' => false,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.profit-pricing.index'))
            ->assertOk()
            ->assertSee('New Egg Product')
            ->assertSee('piece')
            ->assertSee('₱12.00')
            ->assertSee('₱10.00')
            ->assertSee('15.00%')
            ->assertSee('₱11.50')
            ->assertSee('₱1.50')
            ->assertDontSee('>box<');
    }

    public function test_new_rice_products_require_cost_and_default_to_fifteen_percent_profit(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
        ]);

        $productData = [
            'name' => 'New Rice Product',
            'rice_type' => 'Premium',
            'sack_weight_kg' => 10,
            'price_per_kg' => 50,
            'price_per_sack' => 500,
            'low_stock_threshold' => 20,
            'is_active' => 1,
        ];

        $this->actingAs($admin)
            ->from(route('admin.products.create'))
            ->post(route('admin.products.store'), $productData)
            ->assertRedirect(route('admin.products.create'))
            ->assertSessionHasErrors('cost_per_sack');

        $this->post(route('admin.products.store'), [
            ...$productData,
            'cost_per_sack' => 400,
        ])->assertRedirect(route('admin.products.index'));

        $product = Product::where('name', 'New Rice Product')->firstOrFail();
        $this->assertSame('15.00', $product->profit_percentage);

        $this->get(route('admin.profit-pricing.index'))
            ->assertOk()
            ->assertSee('New Rice Product')
            ->assertSee('₱460.00')
            ->assertSee('₱60.00');
    }

    public function test_new_generic_products_require_unit_cost_and_default_to_fifteen_percent_profit(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
        ]);

        $productData = [
            'name' => 'New Generic Product',
            'category' => 'Household',
            'base_unit' => 'piece',
            'initial_stock_quantity' => 10,
            'selling_units' => [
                [
                    'unit_name' => 'piece',
                    'conversion_factor' => 1,
                    'selling_price' => 12,
                ],
            ],
            'low_stock_threshold' => 5,
            'is_active' => 1,
        ];

        $this->actingAs($admin)
            ->from(route('admin.products.create'))
            ->post(route('admin.products.store'), $productData)
            ->assertRedirect(route('admin.products.create'))
            ->assertSessionHasErrors('selling_units.0.cost_price');

        $productData['selling_units'][0]['cost_price'] = 10;

        $this->post(route('admin.products.store'), $productData)
            ->assertRedirect(route('admin.products.index'));

        $product = Product::where('name', 'New Generic Product')->firstOrFail();
        $this->assertSame('15.00', $product->profit_percentage);

        $this->get(route('admin.profit-pricing.index'))
            ->assertOk()
            ->assertSee('New Generic Product')
            ->assertSee('₱11.50')
            ->assertSee('₱1.50');
    }

    public function test_existing_products_without_a_target_profit_use_the_default(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
        ]);

        $product = Product::withoutEvents(function () {
            return Product::create([
                'name' => 'Legacy Rice Product',
                'inventory_type' => 'rice',
                'sack_weight_kg' => 10,
                'price_per_sack' => 100,
                'cost_per_sack' => 80,
                'is_active' => true,
            ]);
        });

        $this->assertNull($product->profit_percentage);

        $this->actingAs($admin)
            ->get(route('admin.profit-pricing.index'))
            ->assertOk()
            ->assertSee('Legacy Rice Product')
            ->assertSee('15.00%')
            ->assertSee('₱92.00')
            ->assertSee('₱12.00');
    }
}
