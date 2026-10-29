<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductSellingUnit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartAddOnTest extends TestCase
{
    use RefreshDatabase;

    public function test_cart_recommends_and_adds_generic_grocery_to_existing_rice_order(): void
    {
        $jasmine = Product::query()->create([
            'name' => 'Jasmine Rice',
            'inventory_type' => 'rice',
            'stock_kg' => 100,
            'low_stock_threshold' => 5,
            'price_per_kg' => 60,
            'price_confirmed' => true,
            'is_active' => true,
        ]);

        $eggs = Product::query()->create([
            'name' => 'Eggs',
            'category' => 'Grocery',
            'inventory_type' => 'generic',
            'stock_unit' => 'piece',
            'base_unit' => 'piece',
            'stock_base_quantity' => 30,
            'stock_quantity' => 30,
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

        $cart = [(string) $jasmine->id => ['unit' => 'kg', 'quantity' => 5]];

        $this->withSession(['cart' => $cart])
            ->get(route('cart.index'))
            ->assertOk()
            ->assertSee('Add something to go with it')
            ->assertSee('Eggs')
            ->assertSee('30.00 piece available');

        $this->post(route('cart.add', $eggs), [
            'unit' => 'piece',
            'quantity' => 1,
        ])->assertRedirect(route('cart.index'));

        $updatedCart = session('cart');
        $this->assertSame(5.0, (float) $updatedCart[$jasmine->id]['quantity']);
        $this->assertSame('piece', $updatedCart[$eggs->id]['unit']);
        $this->assertSame(1.0, (float) $updatedCart[$eggs->id]['quantity']);
    }

    public function test_cart_does_not_recommend_products_already_in_cart_or_without_confirmed_prices(): void
    {
        $inCart = Product::query()->create([
            'name' => 'Jasmine Rice',
            'inventory_type' => 'rice',
            'stock_kg' => 100,
            'price_per_kg' => 60,
            'price_confirmed' => true,
            'is_active' => true,
        ]);

        $unconfirmed = Product::query()->create([
            'name' => 'Unconfirmed Eggs',
            'inventory_type' => 'generic',
            'stock_unit' => 'piece',
            'base_unit' => 'piece',
            'stock_base_quantity' => 10,
            'price_confirmed' => false,
            'is_active' => true,
        ]);

        ProductSellingUnit::query()->create([
            'product_id' => $unconfirmed->id,
            'unit_name' => 'piece',
            'conversion_factor' => 1,
            'selling_price' => 9,
            'is_active' => true,
        ]);

        $this->withSession([
            'cart' => [(string) $inCart->id => ['unit' => 'kg', 'quantity' => 5]],
        ])
            ->get(route('cart.index'))
            ->assertOk()
            ->assertDontSee('Add something to go with it')
            ->assertDontSee('Unconfirmed Eggs');
    }
}