<?php

namespace Tests\Feature\Admin;

use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryForecastTest extends TestCase
{
    use RefreshDatabase;

    public function test_inventory_forecast_uses_recent_sales_to_estimate_days_left_and_restock_gap(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
        ]);
        $product = Product::query()->create([
            'name' => 'Fast Moving Rice',
            'inventory_type' => 'rice',
            'stock_kg' => 2,
            'low_stock_threshold' => 5,
            'is_active' => true,
        ]);

        $movement = InventoryMovement::query()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'movement_type' => 'sale',
            'quantity_kg' => -30,
            'quantity' => -30,
            'unit' => 'kg',
            'sacks' => 0,
            'movement_date' => today(),
            'created_by' => $admin->id,
        ]);

        $this->assertSame(today()->toDateString(), $movement->movement_date->toDateString());
        $this->assertSame(1, InventoryMovement::query()
            ->where('movement_type', 'sale')
            ->whereNotNull('product_id')
            ->whereDate('movement_date', '>=', now()->subDays(29)->toDateString())
            ->whereDate('movement_date', '<=', now()->toDateString())
            ->count());

        $this->actingAs($admin)
            ->get(route('admin.inventory.index'))
            ->assertOk()
            ->assertSee('Sales-Based Stock Forecast')
            ->assertSee('Fast Moving Rice')
            ->assertSee('2.0 days')
            ->assertSee('7.00 kg')
            ->assertSee('5.00 kg')
            ->assertSee('Restock soon');
    }
}