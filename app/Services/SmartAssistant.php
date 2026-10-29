<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Collection;

class SmartAssistant
{
    public function answer(?User $user, string $question): array
    {
        $text = mb_strtolower(trim($question));

        if ($text === '') {
            return $this->reply('I can help with products, ordering, payment, delivery, and order status.');
        }

        if ($this->matches($text, ['payment', 'gcash', 'cash', 'bayad', 'bayaran'])) {
            return $this->reply(
                'Checkout supports cash on delivery or pickup, and GCash. Orders stay pending until the store reviews them and verifies payment. Please confirm the final amount with the store before paying.'
            );
        }

        if ($this->matches($text, ['delivery', 'deliver', 'shipping', 'pickup', 'pick up', 'shipping fee', 'delivery fee'])) {
            return $this->reply(
                'You can choose delivery or pickup at checkout. Delivery fees depend on the delivery area and order size; the store confirms the final fee and total when reviewing your order.'
            );
        }

        if ($this->matches($text, ['track', 'order status', 'my order', 'where is my order', 'status ng order'])) {
            return $this->orderStatus($user);
        }

        if ($this->matches($text, ['how to order', 'how can i order', 'place an order', 'place order', 'paano umorder', 'checkout'])) {
            return $this->reply(
                'Browse the products, add your items to the cart, then continue to checkout. Choose delivery or pickup and a payment method, then submit your order. The order remains pending until the store reviews availability and confirms the final amount.'
            );
        }

        if ($user?->isAdmin() && $this->matches($text, ['inventory', 'stock', 'low stock', 'pending orders', 'summary'])) {
            return $this->operationsSummary();
        }

        if ($this->matches($text, ['recommend', 'suggest', 'product', 'rice', 'bigas', 'available', 'price', 'cheap', 'affordable', 'hanap'])) {
            return $this->recommendProducts($text);
        }

        if ($this->matches($text, ['help', 'faq', 'how to order', 'paano umorder', 'hello', 'hi', 'kamusta'])) {
            return $this->reply(
                'I can help you find available products, explain payment and delivery, or check your recent order status. What would you like to know?',
                [],
                ['Recommend a product', 'Payment options', 'Delivery and pickup', 'Track my order']
            );
        }

        return $this->recommendProducts($text);
    }

    private function orderStatus(?User $user): array
    {
        if (!$user) {
            return $this->reply('Please sign in to check your order status. I can still help you browse products or answer checkout questions.');
        }

        if ($user->isAdmin()) {
            $pending = Order::query()->where('status', 'pending')->count();
            $today = Order::query()->whereDate('created_at', today())->count();

            return $this->reply("There are {$pending} orders awaiting review and {$today} orders created today. Customer details are available in the admin orders page.");
        }

        if (!$user->isCustomer()) {
            return $this->reply('Order tracking is available from the customer account that placed the order.');
        }

        $orders = $user->orders()
            ->latest()
            ->limit(5)
            ->get(['order_number', 'status', 'payment_status', 'created_at', 'total']);

        if ($orders->isEmpty()) {
            return $this->reply('I could not find any orders on your account yet. You can browse products and place an order from the store page.');
        }

        $details = $orders->map(fn (Order $order) => sprintf(
            '%s: %s; payment %s; placed %s; total ₱%s',
            $order->order_number,
            str($order->status)->replace('_', ' ')->title(),
            str($order->payment_status)->replace('_', ' ')->title(),
            $order->created_at->format('M j, Y'),
            number_format((float) $order->total, 2)
        ))->implode("\n");

        return $this->reply("Here are your latest orders:\n{$details}");
    }

    private function operationsSummary(): array
    {
        $products = Product::query()->where('is_active', true)->get();
        $lowStock = $products->filter(function (Product $product): bool {
            if (($product->inventory_type ?? 'rice') === 'generic') {
                return (float) $product->stock_base_quantity
                    <= (float) $product->low_stock_threshold_quantity;
            }

            return (float) $product->stock_kg <= (float) $product->low_stock_threshold;
        });
        $pendingOrders = Order::query()->where('status', 'pending')->count();

        $names = $lowStock->take(5)->pluck('name')->implode(', ');
        $lowStockMessage = $names !== ''
            ? " Low-stock products: {$names}."
            : ' No active products are currently at or below their low-stock threshold.';

        return $this->reply(sprintf(
            'Current store summary: %d active products, %d low-stock products, and %d orders awaiting review.%s',
            $products->count(),
            $lowStock->count(),
            $pendingOrders,
            $lowStockMessage
        ));
    }

    private function recommendProducts(string $question): array
    {
        $products = Product::query()
            ->where('is_active', true)
            ->with(['sellingUnits' => fn ($query) => $query->where('is_active', true)])
            ->orderBy('name')
            ->limit(100)
            ->get();

        $terms = collect(preg_split('/[^\pL\pN]+/u', $question) ?: [])
            ->map(fn (string $term) => mb_strtolower($term))
            ->filter(fn (string $term) => mb_strlen($term) >= 3)
            ->reject(fn (string $term) => in_array($term, [
                'recommend', 'recommendation', 'suggest', 'product', 'available',
                'price', 'cheap', 'affordable', 'please', 'hello', 'there',
                'show', 'find', 'want', 'need', 'with', 'rice', 'bigas',
            ], true))
            ->unique()
            ->values();

        $matches = $products
            ->filter(fn (Product $product) => $this->availableStock($product) > 0)
            ->map(function (Product $product) use ($terms): array {
                $searchable = mb_strtolower(implode(' ', array_filter([
                    $product->name,
                    $product->brand,
                    $product->category,
                    $product->rice_type,
                    $product->description,
                ])));
                $score = $terms->sum(fn (string $term) => str_contains($searchable, $term) ? 1 : 0);

                return [
                    'product' => $product,
                    'score' => $score,
                ];
            });

        if ($terms->isNotEmpty() && $matches->contains(fn (array $match) => $match['score'] > 0)) {
            $matches = $matches
                ->filter(fn (array $match) => $match['score'] > 0)
                ->sortByDesc('score');
        }

        if ($this->matches($question, ['cheap', 'affordable', 'lowest price', 'cheapest'])) {
            $matches = $matches->sortBy(fn (array $match) =>
                $this->recommendationPrice($match['product']) ?? PHP_FLOAT_MAX
            );
        }

        $recommendations = $matches
            ->take(4)
            ->map(fn (array $match) => $this->productCard($match['product']))
            ->values()
            ->all();

        if ($recommendations === []) {
            return $this->reply('I could not find an active in-stock match right now. You can browse the full product list or ask the store to check availability.');
        }

        return $this->reply('Here are active products currently in stock. Prices are shown only when confirmed by the store.', $recommendations);
    }

    private function productCard(Product $product): array
    {
        $generic = ($product->inventory_type ?? 'rice') === 'generic';
        $stock = $this->availableStock($product);
        $unit = $generic
            ? ($product->base_unit ?: $product->stock_unit ?: 'unit')
            : 'kg';
        $price = 'Price pending confirmation';

        if ($product->price_confirmed) {
            if ($generic) {
                $unitPrices = $product->sellingUnits
                    ->filter(fn ($sellingUnit) => (float) $sellingUnit->selling_price > 0)
                    ->take(2)
                    ->map(fn ($sellingUnit) => sprintf(
                        '%s ₱%s',
                        $sellingUnit->unit_name,
                        number_format((float) $sellingUnit->selling_price, 2)
                    ));
                if ($unitPrices->isNotEmpty()) {
                    $price = $unitPrices->implode(' / ');
                } elseif ((float) $product->price_per_unit > 0) {
                    $price = '₱' . number_format((float) $product->price_per_unit, 2) . ' per ' . $unit;
                }
            } elseif ((float) $product->price_per_kg > 0) {
                $price = '₱' . number_format((float) $product->price_per_kg, 2) . ' per kg';
            }
        }

        return [
            'name' => $product->name,
            'category' => $product->rice_type ?: $product->category ?: 'Store product',
            'stock' => number_format($stock, 2) . ' ' . $unit . ' available',
            'price' => $price,
            'image' => $product->image ? asset('storage/' . $product->image) : null,
            'url' => route('home', ['search' => $product->name]),
        ];
    }

    private function availableStock(Product $product): float
    {
        return ($product->inventory_type ?? 'rice') === 'generic'
            ? (float) $product->stock_base_quantity
            : (float) $product->stock_kg;
    }

    private function recommendationPrice(Product $product): ?float
    {
        if (!$product->price_confirmed) {
            return null;
        }

        if (($product->inventory_type ?? 'rice') !== 'generic') {
            return (float) $product->price_per_kg > 0
                ? (float) $product->price_per_kg
                : null;
        }

        $sellingPrice = $product->sellingUnits
            ->map(fn ($unit) => (float) $unit->selling_price)
            ->filter(fn (float $price) => $price > 0)
            ->min();

        $baseUnitPrice = (float) $product->price_per_unit;

        return $sellingPrice ?: ($baseUnitPrice > 0 ? $baseUnitPrice : null);
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

    private function reply(string $message, array $products = [], array $quickReplies = []): array
    {
        return [
            'message' => $message,
            'products' => $products,
            'quick_replies' => $quickReplies,
        ];
    }
}