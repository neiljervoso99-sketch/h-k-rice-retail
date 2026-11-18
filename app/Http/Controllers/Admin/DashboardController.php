<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'period' => ['nullable', Rule::in(['week', 'month', 'year'])],
        ]);
        $chartPeriod = $validated['period'] ?? 'week';

        $today = now()->toDateString();
        $startOfWeek = now()->copy()->startOfWeek()->toDateString();
        $startOfMonth = now()->copy()->startOfMonth()->toDateString();
        $periodStart = match ($chartPeriod) {
            'month' => now()->copy()->startOfMonth(),
            'year' => now()->copy()->startOfYear(),
            default => now()->copy()->startOfWeek(),
        };
        $periodEnd = now()->copy()->endOfDay();
        $previousPeriodEnd = $periodStart->copy()->subSecond();
        $previousPeriodStart = $previousPeriodEnd->copy()
            ->subSeconds((int) $periodStart->diffInSeconds($periodEnd));

        $weekSales = Order::where('status', 'completed')
            ->whereNotNull('completed_at')
            ->whereBetween('completed_at', [
                $startOfWeek,
                now()->copy()->endOfDay(),
            ])
            ->sum('total');

        $monthSales = Order::where('status', 'completed')
            ->whereNotNull('completed_at')
            ->whereBetween('completed_at', [
                $startOfMonth,
                now()->copy()->endOfDay(),
            ])
            ->sum('total');

        $todaySales = Order::where('status', 'completed')
            ->whereNotNull('completed_at')
            ->whereDate('completed_at', $today)
            ->sum('total');

        $pendingOrders = Order::where('status', 'pending')->count();
        $acceptedOrders = Order::where('status', 'accepted')->count();
        $completedOrders = Order::where('status', 'completed')->count();
        $cancelledOrders = Order::where('status', 'cancelled')->count();

        $activeProducts = Product::where('is_active', true)->get();
        $activeProductCount = $activeProducts->count();
        $lowStockProducts = $activeProducts
            ->filter(function ($product) {
                $isGeneric = ($product->inventory_type ?? 'rice') === 'generic';
                $stock = (float) ($isGeneric
                    ? $product->stock_base_quantity
                    : $product->stock_kg);
                $threshold = (float) ($isGeneric
                    ? $product->low_stock_threshold_quantity
                    : $product->low_stock_threshold);

                return $stock <= $threshold;
            });
        $lowStockCount = $lowStockProducts->count();
        $lowStockProducts = $lowStockProducts
            ->sortBy(function ($product) {
                return ($product->inventory_type ?? 'rice') === 'generic'
                    ? (float) $product->stock_base_quantity
                    : (float) $product->stock_kg;
            })
            ->take(6)
            ->values();

        $recentOrders = Order::with('customer')
            ->latest()
            ->take(7)
            ->get();

        $completedOrdersToday = Order::where('status', 'completed')
            ->whereNotNull('completed_at')
            ->whereDate('completed_at', $today)
            ->count();

        $ordersThisMonth = Order::whereBetween('created_at', [
            now()->copy()->startOfMonth(),
            now()->copy()->endOfDay(),
        ])->count();

        $customerCount = User::where('role', 'customer')
            ->where('is_admin', false)
            ->count();
        $adminCount = User::query()
            ->where(fn ($query) => $query
                ->where('role', 'admin')
                ->orWhere('is_admin', true))
            ->count();
        $staffCount = User::where('role', 'staff')
            ->where('is_admin', false)
            ->count();

        $teamMembers = User::query()
            ->where(function ($query) {
                $query->whereIn('role', ['admin', 'staff'])
                    ->orWhere('is_admin', true);
            })
            ->withCount(['activityLogs as recent_activity_count' => fn ($query) => $query
                ->where('created_at', '>=', now()->copy()->subDays(30))])
            ->orderByRaw("CASE WHEN role = 'admin' OR is_admin = 1 THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->take(6)
            ->get();

        $periodRevenue = Order::query()
            ->where('status', 'completed')
            ->whereNotNull('completed_at')
            ->whereBetween('completed_at', [$periodStart, $periodEnd])
            ->sum('total');
        $periodSalesCount = Order::query()
            ->where('status', 'completed')
            ->whereNotNull('completed_at')
            ->whereBetween('completed_at', [$periodStart, $periodEnd])
            ->count();
        $previousPeriodRevenue = Order::query()
            ->where('status', 'completed')
            ->whereNotNull('completed_at')
            ->whereBetween('completed_at', [$previousPeriodStart, $previousPeriodEnd])
            ->sum('total');
        $growthPercentage = (float) $previousPeriodRevenue > 0
            ? (((float) $periodRevenue - (float) $previousPeriodRevenue) / (float) $previousPeriodRevenue) * 100
            : null;

        $chartStart = $periodStart->copy()->startOfDay();
        $chartEnd = $periodEnd->copy();
        $dailySales = Order::query()
            ->where('status', 'completed')
            ->whereNotNull('completed_at')
            ->whereBetween('completed_at', [$chartStart, $chartEnd])
            ->selectRaw('DATE(completed_at) as sales_date, SUM(total) as total, COUNT(*) as sales_count')
            ->groupBy('sales_date')
            ->get()
            ->keyBy('sales_date');

        $salesChartLabels = $salesChartData = $salesCountChartData = [];
        if ($chartPeriod === 'year') {
            $monthlySales = $dailySales->groupBy(
                fn ($sale) => substr((string) $sale->sales_date, 0, 7)
            );

            for ($month = 1; $month <= 12; $month++) {
                $key = now()->format('Y').'-'.str_pad((string) $month, 2, '0', STR_PAD_LEFT);
                $monthSalesRows = $monthlySales->get($key, collect());
                $salesChartLabels[] = now()->copy()->startOfYear()->addMonths($month - 1)->format('M');
                $salesChartData[] = (float) $monthSalesRows->sum('total');
                $salesCountChartData[] = (int) $monthSalesRows->sum('sales_count');
            }
        } else {
            $daysInPeriod = $chartPeriod === 'month' ? now()->day : 7;
            for ($dayOffset = $daysInPeriod - 1; $dayOffset >= 0; $dayOffset--) {
                $date = now()->copy()->subDays($dayOffset);
                $sale = $dailySales->get($date->toDateString());
                $salesChartLabels[] = $chartPeriod === 'week'
                    ? $date->format('D')
                    : $date->format('M j');
                $salesChartData[] = (float) ($sale->total ?? 0);
                $salesCountChartData[] = (int) ($sale->sales_count ?? 0);
            }
        }

        $salesByCategory = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->leftJoin('products', 'products.id', '=', 'order_items.product_id')
            ->where('orders.status', 'completed')
            ->whereNotNull('orders.completed_at')
            ->whereBetween('orders.completed_at', [$periodStart, $periodEnd])
            ->selectRaw("COALESCE(NULLIF(products.category, ''), 'Uncategorized') as category, SUM(order_items.line_total) as total")
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        $topProducts = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', 'completed')
            ->whereNotNull('orders.completed_at')
            ->whereBetween('orders.completed_at', [$periodStart, $periodEnd])
            ->select(
                'order_items.product_name',
                DB::raw('SUM(order_items.quantity) as quantity_sold'),
                DB::raw('SUM(order_items.line_total) as sales_total')
            )
            ->groupBy('order_items.product_name')
            ->orderByDesc('sales_total')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'todaySales',
            'weekSales',
            'monthSales',
            'ordersThisMonth',
            'activeProductCount',
            'customerCount',
            'adminCount',
            'staffCount',
            'teamMembers',
            'pendingOrders',
            'acceptedOrders',
            'completedOrders',
            'cancelledOrders',
            'lowStockCount',
            'lowStockProducts',
            'recentOrders',
            'completedOrdersToday',
            'salesChartLabels',
            'salesChartData',
            'salesCountChartData',
            'chartPeriod',
            'periodRevenue',
            'periodSalesCount',
            'growthPercentage',
            'salesByCategory',
            'topProducts'
        ));
    }
}
