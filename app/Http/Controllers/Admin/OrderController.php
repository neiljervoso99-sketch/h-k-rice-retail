<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductSellingUnit;
use App\Models\User;
use App\Support\OrderEmailNotifier;
use App\Support\LowStockAlertNotifier;
use App\Support\UserAlertNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with(['customer', 'items'])
            ->orderByRaw("
                CASE status
                    WHEN 'pending' THEN 0
                    WHEN 'accepted' THEN 1
                    WHEN 'completed' THEN 2
                    WHEN 'cancelled' THEN 3
                    ELSE 4
                END
            ")
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load([
            'customer',
            'items',
            'payments.verifiedBy',
            'rider',
            'assignedBy',
        ]);

        $riders = User::where('role', 'rider')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return view('admin.orders.show', compact('order', 'riders'));
    }

    public function accept(Order $order, Request $request)
    {
        DB::transaction(function () use ($order, $request) {
            $lockedOrder = Order::whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedOrder->status !== 'pending') {
                throw ValidationException::withMessages([
                    'order' => 'Only pending orders can be accepted.',
                ]);
            }

            if (strtolower((string) $lockedOrder->payment_method) === 'gcash') {
                $verifiedPayment = Payment::where('order_id', $lockedOrder->id)
                    ->where('method', 'gcash')
                    ->where('status', 'verified')
                    ->whereNotNull('verified_by')
                    ->whereNotNull('verified_at')
                    ->whereNotNull('proof_path')
                    ->whereNotNull('reference_number')
                    ->exists();

                if (
                    strtolower((string) $lockedOrder->payment_status) !== 'paid'
                    || !$verifiedPayment
                ) {
                    throw ValidationException::withMessages([
                        'order' => 'This GCash payment must be verified before the order can be approved.',
                    ]);
                }
            }

            if ($lockedOrder->inventory_deducted_at !== null) {
                throw ValidationException::withMessages([
                    'order' => 'Inventory has already been deducted for this order.',
                ]);
            }

            $items = OrderItem::where('order_id', $lockedOrder->id)
                ->orderBy('id')
                ->get();

            if ($items->isEmpty()) {
                throw ValidationException::withMessages([
                    'order' => 'This order has no items and cannot be accepted.',
                ]);
            }

            foreach ($items as $item) {
                if ($item->product_id === null) {
                    throw ValidationException::withMessages([
                        'order' => "Product information is missing for {$item->product_name}.",
                    ]);
                }

                $product = Product::whereKey($item->product_id)->first();

                if (!$product) {
                    throw ValidationException::withMessages([
                        'order' => "Product {$item->product_name} no longer exists.",
                    ]);
                }

                $quantity = round((float) $item->quantity, 2);

                if ($quantity <= 0) {
                    throw ValidationException::withMessages([
                        'order' => "Invalid stock quantity for {$item->product_name}.",
                    ]);
                }

                $isGeneric = ($product->inventory_type ?? 'rice') === 'generic';

                if ($isGeneric) {
                    $unit = strtolower(trim((string) (
                        $item->quantity_unit
                        ?: $item->unit_type
                        ?: $product->stock_unit
                    )));

                    $sellingUnit = ProductSellingUnit::query()
                        ->where('product_id', $product->id)
                        ->whereRaw('LOWER(unit_name) = ?', [$unit])
                        ->first();

                    if (!$sellingUnit) {
                        throw ValidationException::withMessages([
                            'order' => "The selling unit '{$unit}' for {$product->name} could not be found. Please check the product's selling units.",
                        ]);
                    }

                    $factor = (float) $sellingUnit->conversion_factor;

                    if (!is_finite($factor) || $factor <= 0) {
                        throw ValidationException::withMessages([
                            'order' => "The stock conversion for {$product->name} is invalid.",
                        ]);
                    }

                    $baseQuantity = round($quantity * $factor, 3);

                    if (!is_finite($baseQuantity) || $baseQuantity <= 0) {
                        throw ValidationException::withMessages([
                            'order' => "Invalid converted stock quantity for {$product->name}.",
                        ]);
                    }

                    $baseUnit = strtolower(trim((string) (
                        $product->base_unit ?: $product->stock_unit ?: 'piece'
                    )));

                    // Deduct inventory using the product's base unit.
                    $updated = Product::whereKey($product->id)
                        ->where('stock_base_quantity', '>=', $baseQuantity)
                        ->decrement('stock_base_quantity', $baseQuantity);

                    if ($updated !== 1) {
                        $availableStock = (float) $product->fresh()->stock_base_quantity;

                        throw ValidationException::withMessages([
                            'order' => "Insufficient stock for {$product->name}. Available stock: {$availableStock} {$baseUnit}.",
                        ]);
                    }

                    // Keep the legacy stock quantity synchronized for compatibility
                    // with the current generic-product setup, where stock_unit is
                    // the base unit and unit_conversion_factor is 1.
                    if (
                        strtolower(trim((string) $product->stock_unit)) === $baseUnit
                        && (float) $product->unit_conversion_factor === 1.0
                    ) {
                        Product::whereKey($product->id)
                            ->decrement('stock_quantity', $baseQuantity);
                    }

                    // Record the cost for the selling unit used in this order.
                    $item->unit_cost = $sellingUnit->cost_price;
                    $item->save();

                    InventoryMovement::create([
                        'product_id' => $product->id,
                        'product_name' => $item->product_name,
                        'movement_type' => 'sale',
                        'quantity' => -$baseQuantity,
                        'unit' => $baseUnit,
                        'quantity_kg' => null,
                        'sacks' => 0,
                        'movement_date' => now()->toDateString(),
                        'notes' => 'Customer order ' . $lockedOrder->order_number
                            . ' (' . $quantity . ' ' . $unit . ')',
                        'created_by' => $request->user()->id,
                    ]);

                    continue;
                }

                // Rice orders continue to use kilogram-based inventory.
                $quantityKg = round((float) $item->quantity_kg, 2);

                if ($quantityKg <= 0) {
                    throw ValidationException::withMessages([
                        'order' => "Invalid stock quantity for {$item->product_name}.",
                    ]);
                }

                $updated = Product::whereKey($product->id)
                    ->where('stock_kg', '>=', $quantityKg)
                    ->decrement('stock_kg', $quantityKg);

                if ($updated !== 1) {
                    $availableStock = $product->fresh()->stock_kg;

                    throw ValidationException::withMessages([
                        'order' => "Insufficient stock for {$product->name}. Available stock: {$availableStock} kg.",
                    ]);
                }

                // Preserve the existing rice cost snapshot.
                if (
                    $product->cost_per_sack !== null
                    && (float) $product->cost_per_sack >= 0
                    && (int) $product->sack_weight_kg > 0
                ) {
                    $costPerKg = (float) $product->cost_per_sack
                        / (int) $product->sack_weight_kg;

                    $unitCost = $item->unit_type === 'sack'
                        ? (float) $product->cost_per_sack
                        : $costPerKg;

                    $item->unit_cost = round($unitCost, 2);
                    $item->save();
                }

                InventoryMovement::create([
                    'product_id' => $product->id,
                    'product_name' => $item->product_name,
                    'movement_type' => 'sale',
                    'quantity' => -$quantityKg,
                    'unit' => 'kg',
                    'quantity_kg' => -$quantityKg,
                    'sacks' => $item->unit_type === 'sack'
                        ? round((float) $item->quantity, 2)
                        : 0,
                    'movement_date' => now()->toDateString(),
                    'notes' => 'Customer order ' . $lockedOrder->order_number,
                    'created_by' => $request->user()->id,
                ]);
            }

            $lockedOrder->status = 'accepted';
            $lockedOrder->accepted_at = now();
            $lockedOrder->processing_at = $lockedOrder->accepted_at;
            $lockedOrder->inventory_deducted_at = now();
            $lockedOrder->save();
        }, 3);

        $order->items()
            ->whereNotNull('product_id')
            ->pluck('product_id')
            ->unique()
            ->each(function ($productId) {
                $product = Product::find($productId);
                if ($product) {
                    LowStockAlertNotifier::check($product);
                }
            });

        // Send the email only after the transaction succeeds.
        OrderEmailNotifier::send(
            $order->fresh(),
            'accepted'
        );
        $order->loadMissing('customer');
        if ($order->customer) {
            UserAlertNotifier::send(
                $order->customer,
                'Order accepted',
                'Your order ' . $order->order_number . ' has been accepted.',
                route('customer.orders.show', $order),
            );
        }

        return redirect()
            ->route('admin.orders.index')
            ->with(
                'success',
                'Order ' . $order->order_number . ' accepted. Inventory has been updated.'
            );
    }

    public function cancel(Order $order)
    {
        DB::transaction(function () use ($order) {
            $lockedOrder = Order::whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedOrder->status !== 'pending') {
                throw ValidationException::withMessages([
                    'order' => 'Only pending orders can be cancelled. Accepted orders cannot be cancelled through this action.',
                ]);
            }

            if ($lockedOrder->inventory_deducted_at !== null) {
                throw ValidationException::withMessages([
                    'order' => 'This order has an inventory deduction record and cannot be cancelled as pending.',
                ]);
            }

            $lockedOrder->status = 'cancelled';
            $lockedOrder->cancelled_at = now();
            $lockedOrder->save();
        }, 3);

        // Send the email only after the transaction succeeds.
        OrderEmailNotifier::send(
            $order->fresh(),
            'cancelled'
        );
        $order->loadMissing('customer');
        if ($order->customer) {
            UserAlertNotifier::send(
                $order->customer,
                'Order cancelled',
                'Your order ' . $order->order_number . ' has been cancelled.',
                route('customer.orders.show', $order),
            );
        }

        return redirect()
            ->route('admin.orders.index')
            ->with('success', 'Order ' . $order->order_number . ' cancelled.');
    }

    public function complete(Order $order)
    {
        DB::transaction(function () use ($order) {
            $lockedOrder = Order::whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedOrder->status !== 'accepted') {
                throw ValidationException::withMessages([
                    'order' => 'Only accepted orders can be marked as completed.',
                ]);
            }

            if ($lockedOrder->inventory_deducted_at === null) {
                throw ValidationException::withMessages([
                    'order' => 'This order has no inventory deduction record.',
                ]);
            }

            $lockedOrder->status = 'completed';
            $lockedOrder->completed_at = now();
            $lockedOrder->delivered_at = $lockedOrder->completed_at;
            $lockedOrder->save();
        }, 3);

        // Send the email only after the transaction succeeds.
        OrderEmailNotifier::send(
            $order->fresh(),
            'completed'
        );
        $order->loadMissing('customer');
        if ($order->customer) {
            UserAlertNotifier::send(
                $order->customer,
                'Order completed',
                'Your order ' . $order->order_number . ' has been completed.',
                route('customer.orders.show', $order),
            );
        }

        return redirect()
            ->route('admin.orders.index')
            ->with('success', 'Order ' . $order->order_number . ' marked as completed.');
    }

    public function assignRider(Order $order, Request $request)
    {
        $validated = $request->validate([
            'rider_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $rider = User::whereKey($validated['rider_id'])
            ->where('role', 'rider')
            ->first();

        if (!$rider) {
            throw ValidationException::withMessages([
                'rider_id' => 'Please select a valid rider account.',
            ]);
        }

        DB::transaction(function () use ($order, $rider, $request) {
            $lockedOrder = Order::whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedOrder->status !== 'accepted') {
                throw ValidationException::withMessages([
                    'rider_id' => 'Only accepted orders can be assigned to a rider.',
                ]);
            }

            if ($lockedOrder->delivery_proof_path !== null) {
                throw ValidationException::withMessages([
                    'rider_id' => 'A rider cannot be changed after delivery proof has been uploaded.',
                ]);
            }

            $lockedOrder->rider_id = $rider->id;
            $lockedOrder->assigned_by = $request->user()->id;
            $lockedOrder->assigned_at = now();
            $lockedOrder->shipped_at = $lockedOrder->assigned_at;
            $lockedOrder->save();
        }, 3);

        // Notify the customer after the assignment succeeds.
        OrderEmailNotifier::send(
            $order->fresh(),
            'rider_assigned',
            $rider->name
        );
        $order->loadMissing('customer');
        if ($order->customer) {
            UserAlertNotifier::send(
                $order->customer,
                'Rider assigned',
                $rider->name . ' has been assigned to order ' . $order->order_number . '.',
                route('customer.orders.show', $order),
            );
        }
        UserAlertNotifier::send(
            $rider,
            'New delivery assignment',
            'Order ' . $order->order_number . ' has been assigned to you.',
            route('rider.orders.show', $order),
        );

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('success', 'Rider assigned successfully.');
    }

    public function paymentProof(Order $order, Payment $payment)
    {
        abort_unless(
            (int) $payment->order_id === (int) $order->id,
            404
        );

        abort_unless(
            strtolower((string) $payment->method) === 'gcash'
                && filled($payment->proof_path),
            404,
            'No GCash payment proof is available.'
        );

        $disk = Storage::disk('local');

        abort_unless(
            $disk->exists($payment->proof_path),
            404,
            'The payment proof file could not be found.'
        );

        return response()->stream(function () use ($disk, $payment) {
            $stream = $disk->readStream($payment->proof_path);

            if ($stream === false) {
                abort(404, 'The payment proof file could not be read.');
            }

            try {
                fpassthru($stream);
            } finally {
                fclose($stream);
            }
        }, 200, [
            'Content-Type' => $disk->mimeType($payment->proof_path) ?: 'image/jpeg',
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
            'Content-Disposition' => 'inline',
        ]);
    }

    public function verifyPayment(
        Order $order,
        Payment $payment,
        Request $request
    ) {
        DB::transaction(function () use ($order, $payment, $request) {
            $lockedOrder = Order::whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedPayment = Payment::whereKey($payment->id)
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless(
                (int) $lockedPayment->order_id === (int) $lockedOrder->id,
                404
            );

            if ($lockedOrder->status !== 'pending') {
                throw ValidationException::withMessages([
                    'payment' => 'Only payments for pending orders can be verified.',
                ]);
            }

            if (
                strtolower((string) $lockedPayment->method) !== 'gcash'
                || $lockedPayment->status !== 'pending'
            ) {
                throw ValidationException::withMessages([
                    'payment' => 'This payment is not eligible for verification.',
                ]);
            }

            if (
                !filled($lockedPayment->reference_number)
                || !filled($lockedPayment->proof_path)
                || !Storage::disk('local')->exists($lockedPayment->proof_path)
            ) {
                throw ValidationException::withMessages([
                    'payment' => 'A valid GCash reference number and payment proof are required.',
                ]);
            }

            $lockedPayment->status = 'verified';
            $lockedPayment->verified_by = $request->user()->id;
            $lockedPayment->verified_at = now();
            $lockedPayment->save();

            $lockedOrder->payment_status = 'paid';
            $lockedOrder->save();
        }, 3);

        $order->loadMissing('customer');
        if ($order->customer) {
            UserAlertNotifier::send(
                $order->customer,
                'Payment verified',
                'The GCash payment for order ' . $order->order_number . ' was verified.',
                route('customer.orders.show', $order),
            );
        }

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('success', 'GCash payment verified successfully.');
    }

    public function rejectPayment(
        Order $order,
        Payment $payment,
        Request $request
    ) {
        DB::transaction(function () use ($order, $payment) {
            $lockedOrder = Order::whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedPayment = Payment::whereKey($payment->id)
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless(
                (int) $lockedPayment->order_id === (int) $lockedOrder->id,
                404
            );

            if ($lockedOrder->status !== 'pending') {
                throw ValidationException::withMessages([
                    'payment' => 'Only payments for pending orders can be rejected.',
                ]);
            }

            if (
                strtolower((string) $lockedPayment->method) !== 'gcash'
                || $lockedPayment->status !== 'pending'
            ) {
                throw ValidationException::withMessages([
                    'payment' => 'This payment is not eligible for rejection.',
                ]);
            }

            $lockedPayment->status = 'rejected';
            $lockedPayment->verified_by = null;
            $lockedPayment->verified_at = null;
            $lockedPayment->save();

            $lockedOrder->payment_status = 'rejected';
            $lockedOrder->save();
        }, 3);

        $order->loadMissing('customer');
        if ($order->customer) {
            UserAlertNotifier::send(
                $order->customer,
                'Payment needs attention',
                'The GCash payment for order ' . $order->order_number . ' was rejected.',
                route('customer.orders.show', $order),
            );
        }

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('success', 'GCash payment rejected. The order remains pending.');
    }
}