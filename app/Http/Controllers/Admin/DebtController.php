<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CashRegisterMovement;
use App\Models\CashRegisterShift;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DebtController extends Controller
{
    public function index()
    {
        $debts = Order::query()
            ->with([
                'items',
                'payments' => fn ($query) => $query
                    ->where('status', 'verified')
                    ->latest('verified_at'),
            ])
            ->where('payment_method', 'utang')
            ->latest()
            ->paginate(25);

        $totalOwed = (float) Order::query()
            ->where('payment_method', 'utang')
            ->sum('total');
        $totalPaid = (float) Payment::query()
            ->where('status', 'verified')
            ->whereHas('order', fn ($query) => $query->where(
                'payment_method',
                'utang'
            ))
            ->sum('amount');
        $hasOpenShift = CashRegisterShift::query()
            ->where('opened_by', auth()->id())
            ->where('status', 'open')
            ->exists();

        return view('admin.debts.index', [
            'debts' => $debts,
            'totalOwed' => $totalOwed,
            'totalPaid' => $totalPaid,
            'totalBalance' => max(0, round($totalOwed - $totalPaid, 2)),
            'hasOpenShift' => $hasOpenShift,
        ]);
    }

    public function recordCashLoan(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:30'],
            'amount' => ['required', 'numeric', 'gt:0', 'max:100000000', 'decimal:0,2'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($validated, $request) {
            $shift = CashRegisterShift::query()
                ->where('opened_by', $request->user()->id)
                ->where('status', 'open')
                ->lockForUpdate()
                ->first();

            if (! $shift) {
                throw ValidationException::withMessages([
                    'shift' => 'Open a cash drawer shift before recording a cash loan.',
                ]);
            }

            $cashSales = (float) $shift->orders()
                ->where('status', 'completed')
                ->where('payment_method', 'cash')
                ->sum('total');
            $cashIn = (float) $shift->movements()
                ->where('movement_type', 'cash_in')
                ->sum('amount');
            $cashOut = (float) $shift->movements()
                ->where('movement_type', 'cash_out')
                ->sum('amount');
            $availableCash = round(
                (float) $shift->opening_cash + $cashSales + $cashIn - $cashOut,
                2
            );
            $amount = round((float) $validated['amount'], 2);

            if ($amount > $availableCash) {
                throw ValidationException::withMessages([
                    'amount' => 'Cash loan cannot exceed the expected drawer balance of ₱'
                        .number_format($availableCash, 2).'.',
                ]);
            }

            $walkIn = User::query()
                ->where('email', 'walkin@hk-store.local')
                ->lockForUpdate()
                ->first();

            if (! $walkIn) {
                $walkIn = User::create([
                    'name' => 'Walk-in Customer',
                    'email' => 'walkin@hk-store.local',
                    'password' => Hash::make(Str::random(64)),
                    'role' => 'customer',
                    'is_admin' => false,
                ]);
            } elseif (
                $walkIn->name !== 'Walk-in Customer'
                || $walkIn->is_admin
                || $walkIn->role !== 'customer'
            ) {
                throw ValidationException::withMessages([
                    'customer_name' => 'The internal walk-in account needs administrator review.',
                ]);
            }

            $now = now();
            do {
                $orderNumber = 'HK-LOAN-'.$now->format('Ymd').'-'
                    .Str::upper(Str::random(8));
            } while (Order::query()->where('order_number', $orderNumber)->exists());

            $order = Order::create([
                'order_number' => $orderNumber,
                'user_id' => $walkIn->id,
                'cash_register_shift_id' => $shift->id,
                'recipient_name' => $validated['customer_name'],
                'phone' => $validated['customer_phone'] ?? 'N/A',
                'delivery_method' => 'pickup',
                'payment_method' => 'utang',
                'payment_status' => 'pending',
                'status' => 'completed',
                'subtotal' => $amount,
                'delivery_fee' => 0,
                'total' => $amount,
                'customer_notes' => $validated['notes'] ?? null,
                'admin_notes' => 'Cash loan recorded by '.$request->user()->name.'.',
                'completed_at' => $now,
            ]);

            CashRegisterMovement::create([
                'cash_register_shift_id' => $shift->id,
                'created_by' => $request->user()->id,
                'movement_type' => 'cash_out',
                'amount' => $amount,
                'note' => 'Cash loan to '.$validated['customer_name'].' ('.$orderNumber.')',
            ]);
        }, 3);

        return redirect()
            ->route('admin.debts.index', ['saved' => 'cash-loan'])
            ->with('success', 'Cash loan recorded. No inventory was changed.');
    }

    public function recordPayment(Request $request, Order $order)
    {
        abort_unless($order->payment_method === 'utang', 404);

        $validated = $request->validate([
            'amount' => [
                'required',
                'numeric',
                'gt:0',
                'max:100000000',
                'decimal:0,2',
            ],
            'method' => ['required', 'in:cash,gcash'],
            'reference_number' => [
                'required_if:method,gcash',
                'nullable',
                'string',
                'max:100',
            ],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($validated, $request, $order) {
            $lockedOrder = Order::query()
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedOrder->payment_method !== 'utang') {
                throw ValidationException::withMessages([
                    'order' => 'This order is not recorded as debt.',
                ]);
            }

            $paid = (float) $lockedOrder->payments()
                ->where('status', 'verified')
                ->sum('amount');
            $balance = round((float) $lockedOrder->total - $paid, 2);
            $amount = round((float) $validated['amount'], 2);

            if ($amount > $balance) {
                throw ValidationException::withMessages([
                    'amount' => 'Payment cannot exceed the remaining balance of ₱'
                        .number_format($balance, 2).'.',
                ]);
            }

            $shift = null;

            if ($validated['method'] === 'cash') {
                $shift = CashRegisterShift::query()
                    ->where('opened_by', $request->user()->id)
                    ->where('status', 'open')
                    ->lockForUpdate()
                    ->first();

                if (! $shift) {
                    throw ValidationException::withMessages([
                        'method' => 'Open a cash drawer shift before recording a cash payment.',
                    ]);
                }
            }

            $payment = Payment::create([
                'order_id' => $lockedOrder->id,
                'method' => $validated['method'],
                'amount' => $amount,
                'status' => 'verified',
                'reference_number' => $validated['method'] === 'gcash'
                    ? trim($validated['reference_number'])
                    : null,
                'verified_by' => $request->user()->id,
                'verified_at' => now(),
                'notes' => $validated['notes'] ?? 'Utang payment recorded by admin.',
            ]);

            if ($shift) {
                CashRegisterMovement::create([
                    'cash_register_shift_id' => $shift->id,
                    'created_by' => $request->user()->id,
                    'movement_type' => 'cash_in',
                    'amount' => $amount,
                    'note' => 'Utang payment for '.$lockedOrder->order_number,
                ]);
            }

            $newPaid = round($paid + (float) $payment->amount, 2);
            $lockedOrder->payment_status = $newPaid >= (float) $lockedOrder->total
                ? 'paid'
                : 'pending';
            $lockedOrder->save();
        }, 3);

        return redirect()
            ->route('admin.debts.index')
            ->with('success', 'Payment recorded successfully.');
    }
}