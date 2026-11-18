<?php

namespace Tests\Feature\Admin;

use App\Events\UserAlertCreated;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\InventoryMovement;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductSellingUnit;
use App\Models\User;
use App\Support\LowStockAlertNotifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class InventoryTransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_stock_in_and_adjustments_suppress_duplicates_and_reset_after_restocking(): void
    {
        Event::fake([UserAlertCreated::class]);
        $admin = $this->admin();
        $product = $this->riceProduct(4, 5);
        $this->actingAs($admin);

        $this->post(route('admin.inventory.stock-in'), [
            'product_id' => $product->id,
            'inventory_type' => 'rice',
            'sacks' => 0,
            'extra_kg' => 1,
            'movement_date' => now()->toDateString(),
        ])->assertRedirect(route('admin.inventory.index'));
        $this->assertSame(1, $admin->notifications()->count());

        $this->stockIn($product, 1);
        $this->assertSame('6.00', $product->fresh()->stock_kg);
        $this->assertSame(1, $admin->notifications()->count());

        $this->adjust($product, 'adjustment_out', 1);
        $this->adjust($product, 'adjustment_out', 1);
        $this->assertSame('4.00', $product->fresh()->stock_kg);
        $this->assertSame(2, $admin->notifications()->count());

        $this->stockIn($product, 2);
        $this->adjust($product, 'adjustment_out', 2);
        $this->assertSame(3, $admin->notifications()->count());

        $this->adjust($product, 'adjustment_out', 4);
        $this->assertSame('0.00', $product->fresh()->stock_kg);
        $this->assertSame(4, $admin->notifications()->count());
        LowStockAlertNotifier::check($product->fresh());
        $this->assertSame(4, $admin->notifications()->count());

        Event::assertDispatchedTimes(UserAlertCreated::class, 4);

        $this->from(route('admin.inventory.index'))
            ->post(route('admin.inventory.stock-in'), [
                'product_id' => $product->id,
                'inventory_type' => 'generic',
                'quantity' => 2,
                'unit' => 'piece',
                'movement_date' => now()->toDateString(),
            ])
            ->assertSessionHasErrors('product_id');

        $this->assertSame('0.00', $product->fresh()->stock_kg);
        $this->assertSame(4, $admin->notifications()->count());
    }

    public function test_failed_order_acceptance_rolls_back_all_stock_and_movements(): void
    {
        Event::fake([UserAlertCreated::class]);
        Mail::fake();
        $admin = $this->admin();
        $customer = User::factory()->create(['role' => 'customer']);
        $firstProduct = $this->riceProduct(5, 1);
        $secondProduct = $this->riceProduct(1, 5);
        $order = Order::create([
            'order_number' => 'HK-ROLLBACK-ORDER',
            'user_id' => $customer->id,
            'recipient_name' => $customer->name,
            'phone' => '555-0101',
            'delivery_method' => 'pickup',
            'payment_method' => 'cash',
            'payment_status' => 'pending',
            'status' => 'pending',
            'subtotal' => 4,
            'delivery_fee' => 0,
            'total' => 4,
        ]);

        foreach ([$firstProduct, $secondProduct] as $product) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'unit_type' => 'kg',
                'quantity_unit' => 'kg',
                'quantity' => 2,
                'quantity_kg' => 2,
                'unit_price' => 1,
                'line_total' => 2,
            ]);
        }

        $this->actingAs($admin)
            ->from(route('admin.orders.show', $order))
            ->post(route('admin.orders.accept', $order))
            ->assertSessionHasErrors('order');

        $this->assertSame('5.00', $firstProduct->fresh()->stock_kg);
        $this->assertSame('1.00', $secondProduct->fresh()->stock_kg);
        $this->assertSame('pending', $order->fresh()->status);
        $this->assertSame(0, $firstProduct->inventoryMovements()->count());
        $this->assertSame(0, $admin->notifications()->count());
        Event::assertNotDispatched(UserAlertCreated::class);
    }

    public function test_customer_order_acceptance_commits_stock_before_alerting(): void
    {
        Event::fake([UserAlertCreated::class]);
        Mail::fake();
        $admin = $this->admin();
        $customer = User::factory()->create(['role' => 'customer']);
        $product = $this->riceProduct(6, 5);
        $order = Order::create([
            'order_number' => 'HK-TEST-ORDER',
            'user_id' => $customer->id,
            'recipient_name' => $customer->name,
            'phone' => '555-0101',
            'delivery_method' => 'pickup',
            'payment_method' => 'cash',
            'payment_status' => 'pending',
            'status' => 'pending',
            'subtotal' => 2,
            'delivery_fee' => 0,
            'total' => 2,
        ]);
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'unit_type' => 'kg',
            'quantity_unit' => 'kg',
            'quantity' => 2,
            'quantity_kg' => 2,
            'unit_price' => 1,
            'line_total' => 2,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.orders.accept', $order))
            ->assertRedirect(route('admin.orders.index'));

        $this->assertSame('4.00', $product->fresh()->stock_kg);
        $this->assertSame('accepted', $order->fresh()->status);
        $this->assertNotNull($order->fresh()->inventory_deducted_at);
        $this->assertSame(1, $admin->notifications()->count());
        $this->assertSame(1, $customer->notifications()->count());
        Event::assertDispatchedTimes(UserAlertCreated::class, 2);

        $this->from(route('admin.orders.index'))
            ->post(route('admin.orders.accept', $order))
            ->assertSessionHasErrors('order');

        $this->assertSame('4.00', $product->fresh()->stock_kg);
    }

    public function test_generic_stock_movements_store_zero_for_legacy_kg_quantity(): void
    {
        Event::fake([UserAlertCreated::class]);
        $admin = $this->admin();
        $product = Product::create([
            'name' => 'Generic Stock',
            'inventory_type' => 'generic',
            'stock_unit' => 'piece',
            'base_unit' => 'piece',
            'stock_quantity' => 1,
            'stock_base_quantity' => 1,
            'unit_conversion_factor' => 1,
            'low_stock_threshold_quantity' => 2,
            'is_active' => true,
        ]);
        ProductSellingUnit::create([
            'product_id' => $product->id,
            'unit_name' => 'piece',
            'conversion_factor' => 1,
            'selling_price' => 1,
            'is_active' => true,
        ]);
        $this->actingAs($admin);

        $this->post(route('admin.inventory.stock-in'), [
            'product_id' => $product->id,
            'inventory_type' => 'generic',
            'quantity' => 1,
            'unit' => 'piece',
            'movement_date' => now()->toDateString(),
        ])->assertRedirect(route('admin.inventory.index'));

        $this->assertSame('2.000', $product->fresh()->stock_base_quantity);
        $this->assertSame('0.00', $product->inventoryMovements()->firstOrFail()->quantity_kg);
        $this->assertSame(1, $admin->notifications()->count());

        $this->post(route('admin.inventory.adjustment'), [
            'product_id' => $product->id,
            'adjustment_type' => 'adjustment_out',
            'quantity' => 1,
            'unit' => 'piece',
            'movement_date' => now()->toDateString(),
            'reason' => 'physical_count',
        ])->assertRedirect(route('admin.inventory.index'));

        $this->assertSame('1.000', $product->fresh()->stock_base_quantity);
        $this->assertSame(2, $product->inventoryMovements()->count());
        $this->assertSame(1, $admin->notifications()->count());
    }

    public function test_manual_pos_sale_commits_inventory_and_persists_alert(): void
    {
        Event::fake([UserAlertCreated::class]);
        $admin = $this->admin();
        $product = $this->riceProduct(6, 5);

        $this->actingAs($admin)
            ->post(route('admin.sales.shifts.open'), ['opening_cash' => 100])
            ->assertRedirect(route('admin.sales.index'));

        $this->actingAs($admin)
            ->post(route('admin.manual-transactions.store'), [
                'customer_name' => 'Walk-in Buyer',
                'payment_method' => 'cash',
                'cash_received' => 2,
                'items' => [[
                    'product_id' => $product->id,
                    'unit_type' => 'kg',
                    'quantity' => 2,
                    'unit_price' => 1,
                ]],
            ])
            ->assertSessionHasNoErrors();

        $manualSale = Order::query()
            ->where('admin_notes', 'like', '%Manual transaction recorded by%')
            ->latest()
            ->firstOrFail();

        $this->get(route('admin.manual-transactions.receipt', $manualSale))
            ->assertOk()
            ->assertSee('Thermal width')
            ->assertSee('58 mm')
            ->assertSee('80 mm')
            ->assertSee('data-paper-width="80"', false)
            ->assertSee('Receipt No.')
            ->assertSee('Real St., Brgy. Loyonsawang')
            ->assertSee('Subtotal')
            ->assertSee('Amount');

        $this->assertSame('4.00', $product->fresh()->stock_kg);
        $this->assertDatabaseHas('orders', [
            'cash_register_shift_id' => $admin->cashRegisterShifts()->sole()->id,
        ]);
        $this->assertSame(1, $admin->notifications()->count());
        Event::assertDispatchedTimes(UserAlertCreated::class, 1);
    }

    public function test_utang_records_customer_debt_and_deducts_inventory_without_cash_shift(): void
    {
        $admin = $this->admin();
        $product = $this->riceProduct(5, 1);
        $product->update([
            'price_per_kg' => 10,
            'price_confirmed' => true,
        ]);
        $this->actingAs($admin);

        $this->get(route('admin.manual-transactions.create', [
            'payment_method' => 'utang',
        ]))
            ->assertOk()
            ->assertSee('value="utang"', false)
            ->assertSee('selected', false)
            ->assertSee('id="cashPaymentFields" style="display: none;"', false)
            ->assertSee('Record Utang');

        $this->post(route('admin.manual-transactions.store'), [
            'customer_name' => 'Utang Customer',
            'customer_phone' => '09123456789',
            'payment_method' => 'utang',
            'items' => [[
                'product_id' => $product->id,
                'unit_type' => 'kg',
                'quantity' => 2,
                'unit_price' => 10,
            ]],
        ])->assertRedirect(route('admin.debts.index'))
            ->assertSessionHasNoErrors();

        $debt = Order::query()->where('payment_method', 'utang')->firstOrFail();
        $this->assertSame('3.00', $product->fresh()->stock_kg);
        $this->assertSame('pending', $debt->payment_status);
        $this->assertSame(0.0, (float) $debt->payments()->sum('amount'));
        $this->assertDatabaseHas('inventory_movements', [
            'product_id' => $product->id,
            'movement_type' => 'sale',
            'quantity_kg' => -2,
        ]);

        $this->get(route('admin.debts.index'))
            ->assertOk()
            ->assertSee('Utang Customer')
            ->assertSee('₱20.00')
            ->assertSee('Unpaid');

        $this->post(route('admin.debts.payments.store', $debt), [
            'amount' => 5,
            'method' => 'gcash',
            'reference_number' => 'GC-UTANG-1',
        ])->assertRedirect(route('admin.debts.index'));

        $this->get(route('admin.debts.index'))
            ->assertOk()
            ->assertSee('₱5.00')
            ->assertSee('₱15.00')
            ->assertSee('Partial');
        $this->assertSame('pending', $debt->fresh()->payment_status);
        $this->assertSame(1, $debt->payments()->count());

        $this->post(route('admin.sales.shifts.open'), [
            'opening_cash' => 100,
        ])->assertRedirect(route('admin.sales.index'));

        $this->post(route('admin.debts.payments.store', $debt), [
            'amount' => 15,
            'method' => 'cash',
        ])->assertRedirect(route('admin.debts.index'));

        $this->assertSame('paid', $debt->fresh()->payment_status);
        $this->assertSame(2, $debt->payments()->count());
        $this->assertDatabaseHas('cash_register_movements', [
            'cash_register_shift_id' => $admin->cashRegisterShifts()->sole()->id,
            'movement_type' => 'cash_in',
            'amount' => 15,
        ]);
        $this->get(route('admin.debts.index'))
            ->assertOk()
            ->assertSee('Paid')
            ->assertSee('₱0.00');
        $this->assertSame('3.00', $product->fresh()->stock_kg);
    }

    public function test_cash_loan_records_cash_out_without_changing_inventory(): void
    {
        $admin = $this->admin();
        $product = $this->riceProduct(5, 1);
        $this->actingAs($admin)
            ->post(route('admin.sales.shifts.open'), [
                'opening_cash' => 100,
            ])
            ->assertRedirect(route('admin.sales.index'));

        $shift = $admin->cashRegisterShifts()->sole();

        $this->post(route('admin.debts.cash-loans.store'), [
            'customer_name' => 'Cash Borrower',
            'customer_phone' => '09123456789',
            'amount' => 30,
            'notes' => 'Short cash advance',
        ])->assertRedirect(route('admin.debts.index', ['saved' => 'cash-loan']))
            ->assertSessionHasNoErrors();

        $loan = Order::query()->where('payment_method', 'utang')->firstOrFail();
        $this->assertSame('30.00', $loan->total);
        $this->assertSame(0, $loan->items()->count());
        $this->assertSame('5.00', $product->fresh()->stock_kg);
        $this->assertSame(0, InventoryMovement::count());
        $this->assertDatabaseHas('cash_register_movements', [
            'cash_register_shift_id' => $shift->id,
            'movement_type' => 'cash_out',
            'amount' => 30,
        ]);

        $this->get(route('admin.debts.index'))
            ->assertOk()
            ->assertSee('id="utang-drawer"', false)
            ->assertSee('Product Utang')
            ->assertSee('Cash Loan')
            ->assertSee('Cash Borrower')
            ->assertSee('Cash Loan')
            ->assertSee('Short cash advance');

        $this->get(route('admin.sales.index'))
            ->assertOk()
            ->assertViewHas('todaySales', 0.0)
            ->assertViewHas('allTimeSales', 0.0);

        $this->from(route('admin.debts.index'))
            ->post(route('admin.debts.cash-loans.store'), [
                'customer_name' => 'Over-limit Borrower',
                'amount' => 80,
            ])
            ->assertSessionHasErrors('amount');

        $this->assertSame(1, $admin->cashRegisterShifts()->sole()->movements()->count());
        $this->assertSame(1, Order::query()->where('payment_method', 'utang')->count());
        $this->assertSame('5.00', $product->fresh()->stock_kg);
    }

    private function admin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
        ]);
    }

    private function riceProduct(float $stock, float $threshold): Product
    {
        return Product::create([
            'name' => 'Test Rice',
            'inventory_type' => 'rice',
            'stock_kg' => $stock,
            'low_stock_threshold' => $threshold,
            'sack_weight_kg' => 25,
            'qr_code' => 'TEST-'.uniqid(),
            'is_active' => true,
        ]);
    }

    private function stockIn(Product $product, int $quantity): void
    {
        $this->post(route('admin.inventory.stock-in'), [
            'product_id' => $product->id,
            'inventory_type' => 'rice',
            'sacks' => 0,
            'extra_kg' => $quantity,
            'movement_date' => now()->toDateString(),
        ])->assertRedirect(route('admin.inventory.index'));
    }

    private function adjust(
        Product $product,
        string $type,
        int $quantity
    ): void {
        $this->post(route('admin.inventory.adjustment'), [
            'product_id' => $product->id,
            'adjustment_type' => $type,
            'quantity' => $quantity,
            'extra_kg' => 0,
            'unit' => 'kg',
            'movement_date' => now()->toDateString(),
            'reason' => 'physical_count',
        ])->assertRedirect(route('admin.inventory.index'));
    }
}
