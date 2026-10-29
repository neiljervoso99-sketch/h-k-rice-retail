<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerFavoritesTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_save_view_and_remove_a_favorite(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $product = Product::query()->create([
            'name' => 'Jasmine Rice',
            'inventory_type' => 'rice',
            'stock_kg' => 50,
            'price_per_kg' => 60,
            'price_confirmed' => true,
            'is_active' => true,
        ]);

        $this->actingAs($customer)
            ->post(route('customer.favorites.store', $product))
            ->assertRedirect();

        $this->assertDatabaseHas('favorites', [
            'user_id' => $customer->id,
            'product_id' => $product->id,
        ]);

        $this->get(route('customer.favorites.index'))
            ->assertOk()
            ->assertSee('Jasmine Rice')
            ->assertSee('Add to Cart');

        $this->delete(route('customer.favorites.destroy', $product))
            ->assertRedirect();

        $this->assertDatabaseMissing('favorites', [
            'user_id' => $customer->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_admin_cannot_access_customer_favorites(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('customer.favorites.index'))
            ->assertForbidden();
    }
}