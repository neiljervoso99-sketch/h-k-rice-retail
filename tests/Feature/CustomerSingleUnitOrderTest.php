<?php

namespace Tests\Feature;

use App\Events\UserAlertCreated;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductSellingUnit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CustomerSingleUnitOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_add_one_kilogram_of_rice_to_cart(): void
    {
        Mail::fake();

        $customer = User::factory()->create(['role' => 'customer']);
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
        ]);
        $rice = Product::query()->create([
            'name' => 'Jasmine Rice',
            'inventory_type' => 'rice',
            'stock_kg' => 10,
            'price_per_kg' => 65,
            'price_confirmed' => true,
            'is_active' => true,
        ]);

        $this->actingAs($customer)
            ->post(route('cart.add', $rice), [
                'unit' => 'kg',
                'quantity' => 1,
            ])
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('cart_success');

        $this->assertSame(
            ['unit' => 'kg', 'quantity' => 1.0],
            session('cart')[(string) $rice->id]
        );

        $this->post(route('checkout.store'), [
            'recipient_name' => $customer->name,
            'phone' => '09123456789',
            'delivery_method' => 'pickup',
            'municipality' => 'Abuyog',
            'province' => 'Leyte',
            'payment_method' => 'cash',
        ])->assertRedirect();

        $this->assertDatabaseHas('order_items', [
            'product_id' => $rice->id,
            'quantity_unit' => 'kg',
            'quantity' => 1,
            'quantity_kg' => 1,
        ]);

        $notification = $admin->notifications()->sole();
        $this->assertSame(
            route('admin.orders.show', Order::query()->sole(), false),
            $notification->data['url']
        );
        $this->actingAs($admin)
            ->get($notification->data['url'])
            ->assertOk()
            ->assertSee('Order Details');
    }

    public function test_customer_can_order_one_generic_selling_unit_and_sees_thank_you_once(): void
    {
        Mail::fake();

        $customer = User::factory()->create(['role' => 'customer']);
        $eggs = Product::query()->create([
            'name' => 'Egg',
            'category' => 'Eggs',
            'inventory_type' => 'generic',
            'stock_unit' => 'piece',
            'base_unit' => 'piece',
            'stock_base_quantity' => 10,
            'price_confirmed' => true,
            'is_active' => true,
        ]);
        ProductSellingUnit::query()->create([
            'product_id' => $eggs->id,
            'unit_name' => 'piece',
            'conversion_factor' => 1,
            'selling_price' => 8,
            'cost_price' => 5,
            'is_active' => true,
        ]);

        $response = $this->actingAs($customer)
            ->withSession([
                'cart' => [
                    (string) $eggs->id => [
                        'unit' => 'piece',
                        'quantity' => 1,
                    ],
                ],
            ])
            ->post(route('checkout.store'), [
                'recipient_name' => $customer->name,
                'phone' => '09123456789',
                'delivery_method' => 'pickup',
                'municipality' => 'Abuyog',
                'province' => 'Leyte',
                'payment_method' => 'cash',
            ]);

        $response->assertSessionHasNoErrors();
        $order = Order::query()->sole();

        $response->assertRedirect(route('checkout.confirmation', $order));
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $eggs->id,
            'quantity_unit' => 'piece',
            'quantity' => 1,
        ]);

        $confirmationUrl = route('checkout.confirmation', $order);
        $this->get($confirmationUrl)
            ->assertOk()
            ->assertSee('id="thank-you-dialog"', false)
            ->assertSee('Success!')
            ->assertSee('Your order was successful. The store will review and confirm your order.')
            ->assertSee('Go back')
            ->assertSee('background: #15803d');

        $this->get($confirmationUrl)
            ->assertOk()
            ->assertDontSee('id="thank-you-dialog"', false);
    }

    public function test_customer_can_order_half_a_dozen_and_admin_approval_notifies_customer_and_records_inventory(): void
    {
        Mail::fake();

        $customer = User::factory()->create(['role' => 'customer']);
        $eggs = Product::query()->create([
            'name' => 'Eggs',
            'inventory_type' => 'generic',
            'stock_unit' => 'piece',
            'base_unit' => 'piece',
            'stock_base_quantity' => 24,
            'stock_quantity' => 24,
            'low_stock_threshold_quantity' => 1,
            'price_confirmed' => true,
            'is_active' => true,
        ]);
        ProductSellingUnit::query()->create([
            'product_id' => $eggs->id,
            'unit_name' => 'dozen',
            'conversion_factor' => 12,
            'selling_price' => 60,
            'cost_price' => 42,
            'is_active' => true,
        ]);

        $this->actingAs($customer)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Eggs')
            ->assertSee('Quantity choice')
            ->assertSee("new Option('Half ' + unitName, '0.5')", false);

        $this->actingAs($customer)
            ->post(route('cart.add', $eggs), [
                'unit' => 'dozen',
                'quantity' => 0.5,
            ])
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('cart_success');

        $this->assertSame(
            0.5,
            (float) session('cart')[(string) $eggs->id]['quantity']
        );

        $this->get(route('cart.index'))
            ->assertOk()
            ->assertSee('class="quantity-preset"', false)
            ->assertSee("new Option('Half ' + unitName, '0.5')", false)
            ->assertSee('step="0.01"', false);

        $this->post(route('checkout.store'), [
            'recipient_name' => $customer->name,
            'phone' => '09123456789',
            'delivery_method' => 'pickup',
            'municipality' => 'Abuyog',
            'province' => 'Leyte',
            'payment_method' => 'cash',
        ])->assertSessionHasNoErrors();

        $order = Order::query()->sole();
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $eggs->id,
            'quantity_unit' => 'dozen',
            'quantity' => 0.5,
        ]);

        Event::fake([UserAlertCreated::class]);
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.orders.accept', $order))
            ->assertRedirect(route('admin.orders.index'));

        $this->assertSame('18.000', $eggs->fresh()->stock_base_quantity);
        $movement = InventoryMovement::query()
            ->where('product_id', $eggs->id)
            ->where('movement_type', 'sale')
            ->sole();
        $this->assertSame('-6.00', $movement->quantity);
        $this->assertSame('piece', $movement->unit);
        $this->assertSame('0.00', $movement->quantity_kg);

        $notification = $customer->notifications()->sole();
        $this->assertSame('Order approved', $notification->data['title']);
        $this->assertSame(
            'Your order '.$order->order_number.' has been approved. The store is preparing it.',
            $notification->data['message']
        );
        $this->assertSame(
            route('customer.orders.show', $order),
            $notification->data['url']
        );

        Event::assertDispatched(
            UserAlertCreated::class,
            fn (UserAlertCreated $event) => $event->userId === $customer->id
                && $event->title === 'Order approved'
        );
    }

    public function test_customer_can_add_half_a_kilogram_of_rice_to_cart(): void
    {
        Mail::fake();

        $customer = User::factory()->create(['role' => 'customer']);
        $rice = Product::query()->create([
            'name' => 'Jasmine Rice',
            'inventory_type' => 'rice',
            'stock_kg' => 10,
            'price_per_kg' => 65,
            'price_confirmed' => true,
            'is_active' => true,
        ]);

        $this->actingAs($customer)
            ->post(route('cart.add', $rice), [
                'unit' => 'kg',
                'quantity' => 0.5,
            ])
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('cart_success');

        $this->assertSame(
            0.5,
            (float) session('cart')[(string) $rice->id]['quantity']
        );

        $this->post(route('checkout.store'), [
            'recipient_name' => $customer->name,
            'phone' => '09123456789',
            'delivery_method' => 'pickup',
            'municipality' => 'Abuyog',
            'province' => 'Leyte',
            'payment_method' => 'cash',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('order_items', [
            'product_id' => $rice->id,
            'quantity_unit' => 'kg',
            'quantity' => 0.5,
            'quantity_kg' => 0.5,
        ]);

        $order = Order::query()->sole();
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.orders.accept', $order))
            ->assertRedirect(route('admin.orders.index'));

        $this->assertSame('9.50', $rice->fresh()->stock_kg);
        $this->assertDatabaseHas('inventory_movements', [
            'product_id' => $rice->id,
            'movement_type' => 'sale',
            'quantity_kg' => -0.5,
            'unit' => 'kg',
        ]);
    }
}
