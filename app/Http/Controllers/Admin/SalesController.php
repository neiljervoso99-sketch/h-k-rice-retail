<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CashRegisterMovement;
use App\Models\CashRegisterShift;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SalesController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $shift = CashRegisterShift::query()
            ->where('opened_by', $user->id)
            ->where('status', 'open')
            ->with(['movements.creator', 'opener'])
            ->first();

        $manualSales = Order::query()
            ->where('status', 'completed')
            ->whereHas('items')
            ->where(function ($query) {
                $query->whereNotNull('cash_register_shift_id')
                    ->orWhere('admin_notes', 'like', 'Manual transaction%');
            });

        $todayOrders = (clone $manualSales)
            ->whereDate('completed_at', today())
            ->with('items')
            ->get();
        $todaySales = (float) $todayOrders->sum(fn (Order $order) => (float) $order->total);
        $todayProfit = (float) $todayOrders->sum(function (Order $order): float {
            return (float) $order->items->sum(function ($item): float {
                return (float) $item->line_total
                    - ((float) $item->unit_cost * (float) $item->quantity);
            });
        });

        $transactions = (clone $manualSales)
            ->with(['items', 'payments'])
            ->latest('completed_at')
            ->paginate(15);
        $recentShifts = CashRegisterShift::query()
            ->with(['opener', 'closer'])
            ->latest('opened_at')
            ->limit(5)
            ->get();

        $cashIn = $shift
            ? (float) $shift->movements()->where('movement_type', 'cash_in')->sum('amount')
            : 0.0;
        $cashOut = $shift
            ? (float) $shift->movements()->where('movement_type', 'cash_out')->sum('amount')
            : 0.0;
        $cashSales = $shift
            ? (float) $shift->orders()
                ->where('status', 'completed')
                ->where('payment_method', 'cash')
                ->sum('total')
            : 0.0;
        $expectedCash = $shift
            ? round((float) $shift->opening_cash + $cashSales + $cashIn - $cashOut, 2)
            : 0.0;

        $allTimeSales = (float) (clone $manualSales)->sum('total');
        $links = [
            ['label' => 'Invoices & Receipts', 'route' => 'admin.manual-transactions.index'],
        ];
        if ($user->isAdmin()) {
            $links = array_merge($links, [
                ['label' => 'Customer Orders', 'route' => 'admin.orders.index'],
                ['label' => 'Inventory', 'route' => 'admin.inventory.index'],
                ['label' => 'Expenses', 'route' => 'admin.expenses.index'],
                ['label' => 'Suppliers', 'route' => 'admin.suppliers.index'],
                ['label' => 'Purchase Orders', 'route' => 'admin.purchase-orders.index'],
                ['label' => 'Reports', 'route' => 'admin.reports.index'],
            ]);
        }

        return view('admin.sales.index', compact(
            'shift',
            'todaySales',
            'todayProfit',
            'todayOrders',
            'allTimeSales',
            'transactions',
            'recentShifts',
            'cashIn',
            'cashOut',
            'cashSales',
            'expectedCash',
            'links',
        ));
    }

    public function openShift(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'opening_cash' => ['required', 'numeric', 'min:0', 'max:100000000'],
        ]);

        DB::transaction(function () use ($request, $validated): void {
            $user = $request->user()->newQuery()
                ->whereKey($request->user()->id)
                ->lockForUpdate()
                ->firstOrFail();

            $alreadyOpen = CashRegisterShift::query()
                ->where('opened_by', $user->id)
                ->where('status', 'open')
                ->exists();

            if ($alreadyOpen) {
                throw ValidationException::withMessages([
                    'shift' => 'You already have an open cash drawer shift.',
                ]);
            }

            CashRegisterShift::query()->create([
                'opened_by' => $user->id,
                'opening_cash' => round((float) $validated['opening_cash'], 2),
                'status' => 'open',
                'opened_at' => now(),
            ]);
        }, 3);

        return redirect()->route('admin.sales.index')
            ->with('success', 'Cash drawer shift opened.');
    }

    public function recordCashMovement(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'movement_type' => ['required', 'in:cash_in,cash_out'],
            'amount' => ['required', 'numeric', 'gt:0', 'max:100000000'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($request, $validated): void {
            $shift = $this->lockOpenShift($request);
            $amount = round((float) $validated['amount'], 2);

            if ($validated['movement_type'] === 'cash_out') {
                $expectedCash = $this->expectedCash($shift);
                if ($amount > $expectedCash) {
                    throw ValidationException::withMessages([
                        'amount' => 'Cash out cannot exceed the expected drawer balance.',
                    ]);
                }
            }

            CashRegisterMovement::query()->create([
                'cash_register_shift_id' => $shift->id,
                'created_by' => $request->user()->id,
                'movement_type' => $validated['movement_type'],
                'amount' => $amount,
                'note' => $validated['note'] ?? null,
            ]);
        }, 3);

        return redirect()->route('admin.sales.index')
            ->with('success', 'Cash drawer movement recorded.');
    }

    public function closeShift(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'closing_cash' => ['required', 'numeric', 'min:0', 'max:100000000'],
        ]);

        DB::transaction(function () use ($request, $validated): void {
            $shift = $this->lockOpenShift($request);
            $expectedCash = $this->expectedCash($shift);
            $closingCash = round((float) $validated['closing_cash'], 2);

            $shift->update([
                'closed_by' => $request->user()->id,
                'closing_cash' => $closingCash,
                'expected_cash' => $expectedCash,
                'cash_variance' => round($closingCash - $expectedCash, 2),
                'status' => 'closed',
                'closed_at' => now(),
            ]);
        }, 3);

        return redirect()->route('admin.sales.index')
            ->with('success', 'Cash drawer shift closed and totals recorded.');
    }

    private function lockOpenShift(Request $request): CashRegisterShift
    {
        return CashRegisterShift::query()
            ->where('opened_by', $request->user()->id)
            ->where('status', 'open')
            ->lockForUpdate()
            ->firstOr(function () {
                throw ValidationException::withMessages([
                    'shift' => 'Open a cash drawer shift before recording this action.',
                ]);
            });
    }

    private function expectedCash(CashRegisterShift $shift): float
    {
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

        return round((float) $shift->opening_cash + $cashSales + $cashIn - $cashOut, 2);
    }
}
