<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Expense;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class AdminDataAssistant
{
    public function answer(string $question): ?array
    {
        $text = mb_strtolower(trim($question));

        if (
            $this->matches($text, ['overview', 'dashboard', 'store status', 'business status'])
            || ($this->matches($text, ['summary']) && ! $this->matches($text, ['inventory']))
        ) {
            return $this->overview();
        }

        if ($this->isSpecificProductQuestion($text)) {
            return $this->products($text);
        }

        if ($this->matches($text, ['low stock', 'out of stock', 'inventory', 'stock', 'restock', 'movement'])) {
            return $this->inventory($text);
        }

        if ($this->matches($text, ['expense', 'expenses', 'gastos', 'spending', 'spent', 'spend'])) {
            return $this->expenses($text);
        }

        if ($this->matches($text, ['pos', 'manual transaction', 'manual sale', 'cash sale', 'walk-in sale'])) {
            return $this->manualTransactions($text);
        }

        if ($this->matches($text, ['supplier', 'suppliers', 'purchase order', 'purchase orders', 'receiving'])) {
            return $this->purchasing();
        }

        if ($this->matches($text, ['staff activity', 'staff transaction', 'transactions by staff', 'who did', 'activity log', 'audit log', 'staff actions'])) {
            return $this->staffActivity($text);
        }

        if ($this->matches($text, ['payment status', 'pending payments', 'verified payments', 'payment summary', 'payment records', 'gcash sales'])) {
            return $this->payments($text);
        }

        if ($this->matches($text, ['user roles', 'users', 'staff accounts', 'customers count', 'riders count', 'accounts', 'customer', 'rider', 'staff', 'role'])) {
            return $this->users();
        }

        if ($this->matches($text, ['top selling', 'best selling', 'best seller', 'top product', 'sales by', 'sales', 'revenue', 'benta', 'kita'])) {
            return $this->sales($text);
        }

        if (
            preg_match('/\bHK-[A-Z0-9-]+\b/i', $text)
            || $this->matches($text, ['orders', 'order status', 'pending order', 'cancelled order', 'completed order'])
        ) {
            return $this->orders($text);
        }

        if ($this->matches($text, ['products', 'product list', 'catalog', 'price', 'available product'])) {
            return $this->products($text);
        }

        return null;
    }

    private function overview(): array
    {
        $activeProducts = Product::query()->where('is_active', true)->get();
        $lowStockCount = $activeProducts->filter(fn (Product $product) => $this->isLowStock($product))->count();
        [$start, $end, $period] = $this->period('this month');
        $revenue = $this->completedOrders($start, $end)->sum('total');
        $pendingOrders = Order::query()->where('status', 'pending')->count();
        $pendingPurchaseOrders = PurchaseOrder::query()
            ->whereIn('status', ['ordered', 'partially_received'])
            ->count();

        return $this->reply(sprintf(
            'Store overview for %s: %d active products, %d low-stock or out-of-stock products, %d orders awaiting review, ₱%s in completed sales, and %d purchase orders awaiting receipt.',
            $period,
            $activeProducts->count(),
            $lowStockCount,
            $pendingOrders,
            number_format((float) $revenue, 2),
            $pendingPurchaseOrders
        ));
    }

    private function inventory(string $question): array
    {
        if ($this->matches($question, ['movement', 'stock-in', 'stock in', 'stock-out', 'stock out', 'adjustment'])) {
            $movements = InventoryMovement::query()
                ->with('creator:id,name')
                ->latest('movement_date')
                ->latest('id')
                ->limit(8)
                ->get();

            if ($movements->isEmpty()) {
                return $this->reply('There are no inventory movements recorded yet.');
            }

            $details = $movements->map(fn (InventoryMovement $movement) => sprintf(
                '%s — %s, %s %s, %s%s',
                $movement->product_name,
                str($movement->movement_type)->replace('_', ' ')->title(),
                number_format((float) ($movement->quantity ?? $movement->quantity_kg), 2),
                $movement->unit ?: 'kg',
                $movement->movement_date?->format('M j, Y') ?? 'date unavailable',
                $movement->creator ? ' by '.$movement->creator->name : ''
            ))->implode("\n");

            return $this->reply("Recent inventory movements:\n{$details}");
        }

        $products = Product::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->filter(fn (Product $product) => $this->isLowStock($product));

        if ($products->isEmpty()) {
            return $this->reply('There are no active products at or below their low-stock threshold.');
        }

        $details = $products->take(12)->map(fn (Product $product) => sprintf(
            '%s: %s %s remaining (threshold %s %s)',
            $product->name,
            number_format($this->stock($product), 2),
            $this->stockUnit($product),
            number_format($this->threshold($product), 2),
            $this->stockUnit($product)
        ))->implode("\n");

        $remaining = $products->count() > 12
            ? sprintf("\nPlus %d more products.", $products->count() - 12)
            : '';

        return $this->reply(sprintf(
            '%d active products are low or out of stock:\n%s%s',
            $products->count(),
            $details,
            $remaining
        ));
    }

    private function expenses(string $question): array
    {
        [$start, $end, $period] = $this->period($question);
        $query = Expense::query()->whereBetween('spent_at', [
            $start->startOfDay()->toDateTimeString(),
            $end->endOfDay()->toDateTimeString(),
        ]);
        $total = (clone $query)->sum('amount');
        $count = (clone $query)->count();
        $categories = (clone $query)
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $categoryDetails = $categories->isEmpty()
            ? ' No expenses were recorded for this period.'
            : ' By category: '.$categories->map(fn ($row) => sprintf(
                '%s ₱%s',
                $row->category,
                number_format((float) $row->total, 2)
            ))->implode(', ').'.';

        return $this->reply(sprintf(
            'Expenses for %s: %d records totaling ₱%s.%s',
            $period,
            $count,
            number_format((float) $total, 2),
            $categoryDetails
        ));
    }

    private function manualTransactions(string $question): array
    {
        [$start, $end, $period] = $this->period($question);
        $transactions = Order::query()
            ->whereHas('customer', fn (Builder $query) => $query->where('email', 'walkin@hk-store.local'))
            ->whereBetween('created_at', [$start, $end])
            ->latest()
            ->limit(8)
            ->get(['order_number', 'payment_method', 'total', 'created_at']);
        $total = Order::query()
            ->whereHas('customer', fn (Builder $query) => $query->where('email', 'walkin@hk-store.local'))
            ->whereBetween('created_at', [$start, $end])
            ->sum('total');

        if ($transactions->isEmpty()) {
            return $this->reply("No manual POS transactions were recorded for {$period}.");
        }

        $details = $transactions->map(fn (Order $order) => sprintf(
            '%s — ₱%s, %s, %s',
            $order->order_number,
            number_format((float) $order->total, 2),
            str($order->payment_method)->upper(),
            $order->created_at?->format('M j, Y g:i A') ?? 'date unavailable'
        ))->implode("\n");

        return $this->reply(sprintf(
            'Manual POS transactions for %s: %d shown, ₱%s total%s\n%s',
            $period,
            $transactions->count(),
            number_format((float) $total, 2),
            $transactions->count() === 8 ? ' (latest 8)' : '',
            $details
        ));
    }

    private function purchasing(): array
    {
        $statusCounts = PurchaseOrder::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->orderBy('status')
            ->get();
        $openOrders = PurchaseOrder::query()
            ->with('supplier:id,name')
            ->whereIn('status', ['ordered', 'partially_received'])
            ->orderBy('expected_at')
            ->limit(8)
            ->get();
        $activeSuppliers = Supplier::query()->where('is_active', true)->count();

        $statuses = $statusCounts->isEmpty()
            ? 'No purchase orders have been created.'
            : $statusCounts->map(fn ($row) => sprintf(
                '%s: %d',
                str($row->status)->replace('_', ' ')->title(),
                $row->total
            ))->implode(', ');

        $openDetails = $openOrders->isEmpty()
            ? ' There are no open purchase orders.'
            : ' Open orders: '.$openOrders->map(fn (PurchaseOrder $order) => sprintf(
                '%s with %s (%s)',
                $order->order_number,
                $order->supplier?->name ?? 'supplier unavailable',
                str($order->status)->replace('_', ' ')
            ))->implode('; ').'.';

        return $this->reply(sprintf(
            'Purchasing summary: %d active suppliers. Purchase orders by status: %s.%s',
            $activeSuppliers,
            $statuses,
            $openDetails
        ));
    }

    private function payments(string $question): array
    {
        [$start, $end, $period] = $this->period($question);
        $payments = Payment::query()
            ->whereBetween('created_at', [$start, $end])
            ->when(
                $this->matches($question, ['gcash']),
                fn (Builder $query) => $query->where('method', 'gcash')
            );
        $statusCounts = (clone $payments)
            ->selectRaw('status, COUNT(*) as total, SUM(amount) as amount')
            ->groupBy('status')
            ->orderBy('status')
            ->get();

        if ($statusCounts->isEmpty()) {
            return $this->reply("No payment records were found for {$period}.");
        }

        $details = $statusCounts->map(fn ($row) => sprintf(
            '%s: %d (₱%s)',
            str($row->status)->replace('_', ' ')->title(),
            $row->total,
            number_format((float) $row->amount, 2)
        ))->implode(', ');

        return $this->reply(sprintf(
            '%s payment summary for %s: %s.',
            $this->matches($question, ['gcash']) ? 'GCash' : 'All',
            $period,
            $details
        ));
    }

    private function staffActivity(string $question): array
    {
        $logs = ActivityLog::query()
            ->with('user:id,name,role')
            ->when(
                $this->matches($question, ['staff']),
                fn (Builder $query) => $query->whereHas('user', fn (Builder $users) => $users->where('role', 'staff'))
            )
            ->latest('created_at')
            ->limit(10)
            ->get();

        if ($logs->isEmpty()) {
            return $this->reply('No matching staff or admin activity has been recorded yet.');
        }

        $details = $logs->map(fn (ActivityLog $log) => sprintf(
            '%s — %s, %s',
            $log->user?->name ?? 'Deleted user',
            str($log->event)->replace(['.', '_', '-'], ' ')->title(),
            $log->created_at?->format('M j, Y g:i A') ?? 'time unavailable'
        ))->implode("\n");

        return $this->reply("Recent recorded activity (sensitive input is excluded):\n{$details}");
    }

    private function users(): array
    {
        $counts = User::query()
            ->selectRaw("CASE WHEN is_admin = 1 OR role = 'admin' THEN 'admin' ELSE role END as account_role, COUNT(*) as total")
            ->groupBy('account_role')
            ->pluck('total', 'account_role');
        $roles = collect(['admin' => 'Administrators', 'staff' => 'Staff', 'customer' => 'Customers', 'rider' => 'Riders'])
            ->map(fn (string $label, string $role) => $label.': '.(int) ($counts[$role] ?? 0))
            ->implode(', ');

        return $this->reply('Account counts by role: '.$roles.'.');
    }

    private function sales(string $question): array
    {
        [$start, $end, $period] = $this->period($question);
        $completed = $this->completedOrders($start, $end);
        $total = (clone $completed)->sum('total');
        $count = (clone $completed)->count();
        $average = $count > 0 ? (float) $total / $count : 0;
        $reply = sprintf(
            'Completed sales for %s: ₱%s from %d orders (average ₱%s per order).',
            $period,
            number_format((float) $total, 2),
            $count,
            number_format($average, 2)
        );

        if ($this->matches($question, ['top', 'best', 'selling', 'product'])) {
            $topProducts = OrderItem::query()
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->where('orders.status', 'completed')
                ->whereNotNull('orders.completed_at')
                ->whereBetween('orders.completed_at', [$start, $end])
                ->selectRaw('order_items.product_name, SUM(order_items.quantity) as quantity_sold, SUM(order_items.line_total) as sales_total')
                ->groupBy('order_items.product_name')
                ->orderByDesc('sales_total')
                ->limit(5)
                ->get();

            if ($topProducts->isNotEmpty()) {
                $reply .= ' Top products by sales: '.$topProducts->map(fn ($product) => sprintf(
                    '%s (%s sold, ₱%s)',
                    $product->product_name,
                    number_format((float) $product->quantity_sold, 2),
                    number_format((float) $product->sales_total, 2)
                ))->implode('; ').'.';
            }
        }

        return $this->reply($reply);
    }

    private function orders(string $question): array
    {
        if (preg_match('/\bHK-[A-Z0-9-]+\b/i', $question, $matches)) {
            $order = Order::query()->where('order_number', strtoupper($matches[0]))->first();

            if (! $order) {
                return $this->reply('I could not find that order number.');
            }

            return $this->reply(sprintf(
                'Order %s: %s; payment %s; %s; total ₱%s.',
                $order->order_number,
                str($order->status)->replace('_', ' ')->title(),
                str($order->payment_status)->replace('_', ' ')->title(),
                $order->created_at?->format('M j, Y') ?? 'date unavailable',
                number_format((float) $order->total, 2)
            ));
        }

        $counts = Order::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');
        $labels = collect(['pending' => 'Awaiting review', 'accepted' => 'Processing', 'completed' => 'Completed', 'cancelled' => 'Cancelled'])
            ->map(fn (string $label, string $status) => $label.': '.(int) ($counts[$status] ?? 0))
            ->implode(', ');
        [$start, $end, $period] = $this->period($question);
        $createdInPeriod = Order::query()->whereBetween('created_at', [$start, $end])->count();

        return $this->reply(sprintf(
            'Order summary: %s. %d orders were placed %s.',
            $labels,
            $createdInPeriod,
            $period
        ));
    }

    private function products(string $question): array
    {
        $products = Product::query()
            ->where('is_active', true)
            ->with(['sellingUnits' => fn ($query) => $query->where('is_active', true)])
            ->orderBy('name')
            ->get();
        $terms = collect(preg_split('/[^\pL\pN]+/u', $question) ?: [])
            ->map(fn (string $term) => mb_strtolower($term))
            ->filter(fn (string $term) => mb_strlen($term) >= 3)
            ->reject(fn (string $term) => in_array($term, [
                'product', 'products', 'price', 'stock', 'available', 'show', 'list',
                'what', 'which', 'have', 'active', 'tell', 'about', 'inventory',
            ], true))
            ->values();

        $matched = $products->map(function (Product $product) use ($terms) {
            $searchable = mb_strtolower(implode(' ', array_filter([
                $product->name,
                $product->brand,
                $product->category,
                $product->rice_type,
                $product->description,
            ])));

            return [
                'product' => $product,
                'score' => $terms->sum(fn (string $term) => str_contains($searchable, $term) ? 1 : 0),
            ];
        });

        if ($terms->isNotEmpty()) {
            $matched = $matched->filter(fn (array $item) => $item['score'] > 0)->sortByDesc('score');
        }

        $matched = $matched->take(10)->values();
        if ($matched->isEmpty()) {
            return $this->reply('I could not find an active product matching that description.');
        }

        $details = $matched->map(fn (array $item) => $this->productDetails($item['product']))->implode("\n");

        return $this->reply(sprintf(
            '%d matching active product%s%s:\n%s',
            $matched->count(),
            $matched->count() === 1 ? '' : 's',
            $products->count() > $matched->count() && $terms->isEmpty()
                ? sprintf(' (showing the first %d of %d)', $matched->count(), $products->count())
                : '',
            $details
        ));
    }

    private function isSpecificProductQuestion(string $question): bool
    {
        if (! $this->matches($question, ['stock', 'price', 'inventory level', 'how many'])) {
            return false;
        }

        $terms = $this->productTerms($question);
        if ($terms->isEmpty()) {
            return false;
        }

        return Product::query()
            ->where('is_active', true)
            ->get(['name', 'brand', 'category', 'rice_type', 'description'])
            ->contains(function (Product $product) use ($terms): bool {
                $searchable = mb_strtolower(implode(' ', array_filter([
                    $product->name,
                    $product->brand,
                    $product->category,
                    $product->rice_type,
                    $product->description,
                ])));

                return $terms->contains(fn (string $term) => str_contains($searchable, $term));
            });
    }

    private function productTerms(string $question)
    {
        return collect(preg_split('/[^\pL\pN]+/u', $question) ?: [])
            ->map(fn (string $term) => mb_strtolower($term))
            ->filter(fn (string $term) => mb_strlen($term) >= 3)
            ->reject(fn (string $term) => in_array($term, [
                'product', 'products', 'price', 'stock', 'available', 'show', 'list',
                'what', 'which', 'have', 'active', 'tell', 'about', 'inventory',
                'level', 'how', 'many', 'much', 'the', 'for', 'with', 'and',
            ], true))
            ->values();
    }

    private function productDetails(Product $product): string
    {
        $generic = ($product->inventory_type ?? 'rice') === 'generic';
        $unit = $generic ? ($product->base_unit ?: $product->stock_unit ?: 'unit') : 'kg';
        $stock = $this->stock($product);
        $price = $generic
            ? $product->sellingUnits->map(fn ($sellingUnit) => $sellingUnit->unit_name.' ₱'.number_format((float) $sellingUnit->selling_price, 2))->implode(', ')
            : ((float) $product->price_per_kg > 0 ? '₱'.number_format((float) $product->price_per_kg, 2).' per kg' : 'price not set');

        if (! $product->price_confirmed) {
            $price = 'price pending confirmation';
        }

        return sprintf(
            '%s: %s %s in stock; threshold %s %s; %s; %s',
            $product->name,
            number_format($stock, 2),
            $unit,
            number_format($this->threshold($product), 2),
            $unit,
            $price,
            $this->isLowStock($product) ? 'LOW STOCK' : 'in stock'
        );
    }

    private function completedOrders(Carbon $start, Carbon $end): Builder
    {
        return Order::query()
            ->where('status', 'completed')
            ->whereNotNull('completed_at')
            ->whereBetween('completed_at', [$start, $end]);
    }

    private function period(string $question): array
    {
        $now = now();
        if ($this->matches($question, ['yesterday', 'kahapon'])) {
            $start = $now->copy()->subDay()->startOfDay();
            $end = $now->copy()->subDay()->endOfDay();
            $label = 'yesterday';
        } elseif ($this->matches($question, ['today', 'ngayon'])) {
            $start = $now->copy()->startOfDay();
            $end = $now->copy()->endOfDay();
            $label = 'today';
        } elseif ($this->matches($question, ['this week', 'week', 'weekly', 'linggo'])) {
            $start = $now->copy()->startOfWeek();
            $end = $now->copy()->endOfWeek();
            $label = 'this week';
        } elseif ($this->matches($question, ['this year', 'year to date'])) {
            $start = $now->copy()->startOfYear();
            $end = $now->copy()->endOfDay();
            $label = 'this year';
        } elseif ($this->matches($question, ['all time', 'all-time', 'ever', 'overall'])) {
            $start = Carbon::create(2000, 1, 1)->startOfDay();
            $end = $now->copy()->endOfDay();
            $label = 'all time';
        } else {
            $start = $now->copy()->startOfMonth();
            $end = $now->copy()->endOfDay();
            $label = 'this month';
        }

        return [$start, $end, $label];
    }

    private function isLowStock(Product $product): bool
    {
        return $this->stock($product) <= $this->threshold($product);
    }

    private function stock(Product $product): float
    {
        return ($product->inventory_type ?? 'rice') === 'generic'
            ? (float) $product->stock_base_quantity
            : (float) $product->stock_kg;
    }

    private function threshold(Product $product): float
    {
        return ($product->inventory_type ?? 'rice') === 'generic'
            ? (float) $product->low_stock_threshold_quantity
            : (float) $product->low_stock_threshold;
    }

    private function stockUnit(Product $product): string
    {
        return ($product->inventory_type ?? 'rice') === 'generic'
            ? (string) ($product->base_unit ?: $product->stock_unit ?: 'unit')
            : 'kg';
    }

    private function matches(string $text, array $keywords): bool
    {
        foreach ($keywords as $keyword) {
            if (str_contains($text, $keyword)) {
                return true;
            }
        }

        return false;
    }

    private function reply(string $message): array
    {
        return [
            'message' => $message,
            'products' => [],
            'quick_replies' => [],
        ];
    }
}
