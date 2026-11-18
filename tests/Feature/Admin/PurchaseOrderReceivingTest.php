<?php

namespace Tests\Feature\Admin;

use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class PurchaseOrderReceivingTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_administrators_can_manage_suppliers_and_purchase_orders(): void
    {
        $staff = User::factory()->create([
            'role' => 'staff',
            'is_admin' => false,
        ]);

        $this->actingAs($staff)
            ->get(route('admin.suppliers.index'))
            ->assertForbidden();
        $this->get(route('admin.purchase-orders.create'))
            ->assertForbidden();
        $this->delete(route('admin.purchase-orders.destroy-all'))
            ->assertForbidden();
    }

    public function test_supplier_crud_and_purchase_order_partial_receiving_are_idempotent(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
        ]);
        $this->actingAs($admin);
        $this->get(route('admin.suppliers.create'))->assertOk();

        $this->post(route('admin.suppliers.store'), [
            'name' => 'Rice Supply Co.',
            'contact_person' => 'Alex Supplier',
            'email' => 'supplier@example.test',
            'phone' => '555-0100',
            'is_active' => 1,
        ])->assertRedirect(route('admin.suppliers.index'));

        $supplier = Supplier::where('name', 'Rice Supply Co.')->firstOrFail();
        $this->put(route('admin.suppliers.update', $supplier), [
            'name' => 'Rice Supply Company',
            'contact_person' => 'Alex Supplier',
            'email' => 'supplier@example.test',
            'phone' => '555-0100',
            'is_active' => 1,
        ])->assertRedirect(route('admin.suppliers.index'));
        $this->get(route('admin.suppliers.index'))
            ->assertRedirect(route('admin.purchase-orders.index'));
        $this->get(route('admin.purchase-orders.index'))
            ->assertOk()
            ->assertSee('Rice Supply Company')
            ->assertSee('Purchase Orders')
            ->assertSee('Suppliers');
        $this->get(route('admin.suppliers.edit', $supplier))->assertOk();
        $this->get(route('admin.purchase-orders.create'))->assertOk();

        $product = Product::create([
            'name' => 'Receiving Rice',
            'inventory_type' => 'rice',
            'stock_kg' => 0,
            'low_stock_threshold' => 5,
            'is_active' => true,
        ]);

        $this->post(route('admin.purchase-orders.store'), [
            'supplier_id' => $supplier->id,
            'expected_at' => now()->addDays(3)->toDateString(),
            'items' => [[
                'product_id' => $product->id,
                'ordered_quantity' => 10,
                'unit_cost' => 25,
            ]],
        ])->assertSessionHasNoErrors();

        $purchaseOrder = PurchaseOrder::firstOrFail();
        $this->get(route('admin.purchase-orders.index'))->assertOk();
        $this->get(route('admin.purchase-orders.show', $purchaseOrder))->assertOk();
        $purchaseOrderItem = PurchaseOrderItem::firstOrFail();
        $firstKey = (string) Str::uuid();
        $receiptRequest = [
            'idempotency_key' => $firstKey,
            'received_at' => now()->toDateString(),
            'items' => [$purchaseOrderItem->id => 4],
        ];

        $this->post(
            route('admin.purchase-orders.receive', $purchaseOrder),
            $receiptRequest
        )->assertRedirect(route('admin.purchase-orders.show', $purchaseOrder));

        $this->assertSame('4.00', $product->fresh()->stock_kg);
        $this->assertSame('4.000', $purchaseOrderItem->fresh()->received_quantity);
        $this->assertSame('partially_received', $purchaseOrder->fresh()->status);
        $this->assertSame(1, $purchaseOrder->receipts()->count());
        $this->assertSame(1, InventoryMovement::count());
        $this->get(route('admin.inventory.index'))
            ->assertOk()
            ->assertSee('Supplier: Rice Supply Company');

        $this->post(
            route('admin.purchase-orders.receive', $purchaseOrder),
            $receiptRequest
        )->assertRedirect(route('admin.purchase-orders.show', $purchaseOrder));

        $this->assertSame('4.00', $product->fresh()->stock_kg);
        $this->assertSame(1, $purchaseOrder->receipts()->count());
        $this->assertSame(1, InventoryMovement::count());

        $this->post(route('admin.purchase-orders.receive', $purchaseOrder), [
            'idempotency_key' => (string) Str::uuid(),
            'received_at' => now()->toDateString(),
            'items' => [$purchaseOrderItem->id => 6],
        ])->assertRedirect(route('admin.purchase-orders.show', $purchaseOrder));

        $this->assertSame('10.00', $product->fresh()->stock_kg);
        $this->assertSame('10.000', $purchaseOrderItem->fresh()->received_quantity);
        $this->assertSame('received', $purchaseOrder->fresh()->status);
        $this->assertSame(2, $purchaseOrder->receipts()->count());
        $this->assertSame(2, InventoryMovement::count());
        $this->get(route('admin.purchase-orders.show', $purchaseOrder))
            ->assertOk()
            ->assertSee('Receiving History');

        $this->from(route('admin.purchase-orders.show', $purchaseOrder))
            ->post(route('admin.purchase-orders.receive', $purchaseOrder), [
                'idempotency_key' => (string) Str::uuid(),
                'received_at' => now()->toDateString(),
                'items' => [$purchaseOrderItem->id => 1],
            ])
            ->assertSessionHasErrors();

        $this->assertSame('10.00', $product->fresh()->stock_kg);
        $this->assertSame(2, $purchaseOrder->receipts()->count());
        $this->assertSame(2, InventoryMovement::count());

        $this->from(route('admin.suppliers.index'))
            ->delete(route('admin.suppliers.destroy', $supplier))
            ->assertSessionHasErrors('supplier');
        $this->assertDatabaseHas('suppliers', ['id' => $supplier->id]);
    }

    public function test_generic_purchase_order_receiving_updates_base_and_legacy_stock(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
        ]);
        $supplier = Supplier::create(['name' => 'Generic Goods']);
        $product = Product::create([
            'name' => 'Generic Item',
            'inventory_type' => 'generic',
            'stock_unit' => 'piece',
            'base_unit' => 'piece',
            'stock_quantity' => 2,
            'stock_base_quantity' => 2,
            'unit_conversion_factor' => 1,
            'low_stock_threshold_quantity' => 1,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.purchase-orders.store'), [
                'supplier_id' => $supplier->id,
                'items' => [[
                    'product_id' => $product->id,
                    'ordered_quantity' => 3,
                ]],
            ])
            ->assertSessionHasNoErrors();

        $purchaseOrder = PurchaseOrder::firstOrFail();
        $item = $purchaseOrder->items()->firstOrFail();

        $this->post(route('admin.purchase-orders.receive', $purchaseOrder), [
            'idempotency_key' => (string) Str::uuid(),
            'received_at' => now()->toDateString(),
            'items' => [$item->id => 2.125],
        ])->assertRedirect(route('admin.purchase-orders.show', $purchaseOrder))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('purchase_order_receipt_items', [
            'purchase_order_item_id' => $item->id,
            'quantity' => 2.125,
        ]);

        $this->assertSame('4.125', $product->fresh()->stock_base_quantity);
        $this->assertSame('4.13', $product->fresh()->stock_quantity);
        $this->assertSame('piece', InventoryMovement::firstOrFail()->unit);
        $this->assertSame('0.00', InventoryMovement::firstOrFail()->quantity_kg);
    }

    public function test_admin_can_delete_all_purchase_orders_while_preserving_inventory(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
        ]);
        $this->actingAs($admin);
        $supplier = Supplier::create(['name' => 'Preserved Supplier']);
        $product = Product::create([
            'name' => 'Preserved Stock',
            'inventory_type' => 'rice',
            'stock_kg' => 0,
            'low_stock_threshold' => 5,
            'is_active' => true,
        ]);

        $receivedOrder = PurchaseOrder::create([
            'order_number' => 'HK-PO-DELETE-1',
            'supplier_id' => $supplier->id,
            'status' => 'ordered',
            'created_by' => $admin->id,
        ]);
        $receivedItem = PurchaseOrderItem::create([
            'purchase_order_id' => $receivedOrder->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'ordered_quantity' => 5,
            'received_quantity' => 0,
            'unit' => 'kg',
            'unit_cost' => 10,
        ]);
        $openOrder = PurchaseOrder::create([
            'order_number' => 'HK-PO-DELETE-2',
            'supplier_id' => $supplier->id,
            'status' => 'ordered',
            'created_by' => $admin->id,
        ]);
        PurchaseOrderItem::create([
            'purchase_order_id' => $openOrder->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'ordered_quantity' => 2,
            'received_quantity' => 0,
            'unit' => 'kg',
            'unit_cost' => 10,
        ]);

        $this->post(route('admin.purchase-orders.receive', $receivedOrder), [
            'idempotency_key' => (string) Str::uuid(),
            'received_at' => now()->toDateString(),
            'items' => [$receivedItem->id => 4],
        ])->assertRedirect(route('admin.purchase-orders.show', $receivedOrder));

        $this->get(route('admin.purchase-orders.index'))
            ->assertOk()
            ->assertSee('Are you sure you want to delete?')
            ->assertSee('Please print your transactions before deleting.');

        $movement = InventoryMovement::firstOrFail();
        Storage::fake('local');
        Storage::disk('local')->put('payment-proofs/payment.jpg', 'payment proof');
        Storage::disk('local')->put('delivery-proofs/delivery.jpg', 'delivery proof');

        $customer = User::factory()->create([
            'role' => 'customer',
            'is_admin' => false,
        ]);
        $customerOrder = Order::create([
            'order_number' => 'HK-SALE-DELETE-1',
            'user_id' => $customer->id,
            'recipient_name' => 'Customer Buyer',
            'phone' => '555-0101',
            'delivery_method' => 'delivery',
            'payment_method' => 'gcash',
            'payment_status' => 'paid',
            'status' => 'completed',
            'subtotal' => 10,
            'delivery_fee' => 0,
            'total' => 10,
            'delivery_proof_path' => 'delivery-proofs/delivery.jpg',
            'inventory_deducted_at' => now(),
        ]);
        OrderItem::create([
            'order_id' => $customerOrder->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 1,
            'quantity_kg' => 1,
            'unit_type' => 'kg',
            'quantity_unit' => 'kg',
            'unit_price' => 10,
            'line_total' => 10,
        ]);
        Payment::create([
            'order_id' => $customerOrder->id,
            'method' => 'gcash',
            'amount' => 10,
            'status' => 'verified',
            'proof_path' => 'payment-proofs/payment.jpg',
        ]);
        ProductReview::create([
            'product_id' => $product->id,
            'user_id' => $customer->id,
            'order_id' => $customerOrder->id,
            'rating' => 5,
            'review' => 'Good product',
            'is_approved' => true,
        ]);

        $manualOrder = Order::create([
            'order_number' => 'HK-SALE-DELETE-2',
            'user_id' => $admin->id,
            'recipient_name' => 'Walk-in Customer',
            'phone' => '555-0102',
            'delivery_method' => 'pickup',
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'status' => 'completed',
            'subtotal' => 10,
            'delivery_fee' => 0,
            'total' => 10,
            'admin_notes' => 'Manual transaction recorded',
            'inventory_deducted_at' => now(),
        ]);
        OrderItem::create([
            'order_id' => $manualOrder->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 1,
            'quantity_kg' => 1,
            'unit_type' => 'kg',
            'quantity_unit' => 'kg',
            'unit_price' => 10,
            'line_total' => 10,
        ]);

        $this->delete(route('admin.purchase-orders.destroy-all'))
            ->assertRedirect(route('admin.purchase-orders.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseCount('purchase_orders', 0);
        $this->assertDatabaseCount('purchase_order_items', 0);
        $this->assertDatabaseCount('purchase_order_receipts', 0);
        $this->assertDatabaseCount('purchase_order_receipt_items', 0);
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);
        $this->assertDatabaseCount('payments', 0);
        $this->assertDatabaseHas('inventory_movements', [
            'id' => $movement->id,
            'purchase_order_receipt_item_id' => null,
        ]);
        $this->assertSame('4.00', $product->fresh()->stock_kg);
        $this->assertDatabaseHas('suppliers', ['id' => $supplier->id]);
        $this->assertDatabaseHas('product_reviews', [
            'product_id' => $product->id,
            'user_id' => $customer->id,
            'order_id' => null,
            'review' => 'Good product',
        ]);
        Storage::disk('local')->assertMissing('payment-proofs/payment.jpg');
        Storage::disk('local')->assertMissing('delivery-proofs/delivery.jpg');
    }
}
