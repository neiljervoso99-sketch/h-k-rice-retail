<?php

namespace Tests\Feature;

use App\Events\UserAlertCreated;
use App\Models\Product;
use App\Models\User;
use App\Support\LowStockAlertNotifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class LowStockAlertTest extends TestCase
{
    use RefreshDatabase;

    public function test_low_and_out_of_stock_alerts_broadcast_once_per_threshold_crossing(): void
    {
        Event::fake([UserAlertCreated::class]);
        $admin = User::factory()->create(['role' => 'admin', 'is_admin' => true]);
        $product = Product::query()->create([
            'name' => 'Alert Rice',
            'inventory_type' => 'rice',
            'stock_kg' => 3,
            'low_stock_threshold' => 5,
            'is_active' => true,
        ]);

        LowStockAlertNotifier::check($product);
        LowStockAlertNotifier::check($product->fresh());

        $this->assertSame(1, $admin->notifications()->count());

        $product->stock_kg = 0;
        $product->save();
        LowStockAlertNotifier::check($product->fresh());
        $this->assertSame(2, $admin->notifications()->count());

        $product->stock_kg = 12;
        $product->save();
        LowStockAlertNotifier::check($product->fresh());

        $product->stock_kg = 3;
        $product->save();
        LowStockAlertNotifier::check($product->fresh());
        $this->assertSame(3, $admin->notifications()->count());
    }
}