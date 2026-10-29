<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductSellingUnit;
use Illuminate\Http\Request;

class CartController extends Controller
{
    private const MINIMUM_KG_ORDER = 5;
    private const MAXIMUM_QUANTITY = 1000;

    private function isGeneric(Product $product): bool
    {
        return ($product->inventory_type ?? 'rice') === 'generic';
    }

    private function activeSellingUnits(Product $product)
    {
        return $product->sellingUnits
            ->where('is_active', true)
            ->values();
    }

    private function sellingUnit(
        Product $product,
        string $unit
    ): ?ProductSellingUnit {
        return $this->activeSellingUnits($product)
            ->first(fn ($sellingUnit) =>
                strtolower($sellingUnit->unit_name) === strtolower($unit)
            );
    }

    private function productUnit(Product $product): string
    {
        if (!$this->isGeneric($product)) {
            return 'kg';
        }

        return (string) ($product->stock_unit ?: 'piece');
    }

    private function validPrice(Product $product, string $unit): ?float
    {
        if ($this->isGeneric($product)) {
            $sellingUnit = $this->sellingUnit($product, $unit);

            if (!$sellingUnit) {
                return null;
            }

            $price = $sellingUnit->selling_price;
        } else {
            $price = match ($unit) {
                'kg' => $product->price_per_kg,
                'sack' => $product->price_per_sack,
                default => null,
            };
        }

        if (
            $price === null
            || !is_numeric($price)
            || (float) $price <= 0
            || !(bool) $product->price_confirmed
        ) {
            return null;
        }

        return (float) $price;
    }

    private function quantityInKg(
        Product $product,
        string $unit,
        float $quantity
    ): ?float {
        if ($this->isGeneric($product)) {
            return null;
        }

        if ($unit === 'kg') {
            return $quantity;
        }

        if ($unit !== 'sack') {
            return null;
        }

        $weight = (float) $product->sack_weight_kg;

        if ($weight <= 0) {
            return null;
        }

        return $quantity * $weight;
    }

    private function genericQuantityInBaseUnits(
        Product $product,
        string $unit,
        float $quantity
    ): ?float {
        $sellingUnit = $this->sellingUnit($product, $unit);

        if (!$sellingUnit) {
            return null;
        }

        $factor = (float) $sellingUnit->conversion_factor;

        if (!is_finite($factor) || $factor <= 0) {
            return null;
        }

        $baseQuantity = $quantity * $factor;

        if (!is_finite($baseQuantity) || $baseQuantity <= 0) {
            return null;
        }

        return round($baseQuantity, 3);
    }

    private function stockAvailable(
        Product $product,
        string $unit,
        float $quantity
    ): bool {
        if ($this->isGeneric($product)) {
            $requestedBaseQuantity = $this->genericQuantityInBaseUnits(
                $product,
                $unit,
                $quantity
            );

            return $requestedBaseQuantity !== null
                && (float) $product->stock_base_quantity > 0
                && $requestedBaseQuantity
                    <= (float) $product->stock_base_quantity;
        }

        $quantityKg = $this->quantityInKg(
            $product,
            $unit,
            $quantity
        );

        return $quantityKg !== null
            && (float) $product->stock_kg > 0
            && $quantityKg <= (float) $product->stock_kg;
    }

    private function validateCartQuantity(
        Product $product,
        string $unit,
        float $quantity
    ): ?string {
        if ($quantity <= 0 || $quantity > self::MAXIMUM_QUANTITY) {
            return 'Quantity must be greater than zero and no more than 1,000 units.';
        }

        if ($this->isGeneric($product)) {
            $sellingUnit = $this->sellingUnit($product, $unit);

            if (!$sellingUnit || (float) $sellingUnit->conversion_factor <= 0) {
                return 'Please select an active selling unit for this product.';
            }

            return null;
        }

        if (!in_array($unit, ['kg', 'sack'], true)) {
            return 'Rice can only be ordered by kilogram or sack.';
        }

        if ($unit === 'kg' && $quantity < self::MINIMUM_KG_ORDER) {
            return 'Minimum order is 5 kg for per-kilogram rice purchases.';
        }

        if ($unit === 'sack' && floor($quantity) !== $quantity) {
            return 'Sack quantities must be whole numbers.';
        }

        if (
            $unit === 'kg'
            && floor($quantity) !== $quantity
        ) {
            return 'Rice quantities in kilograms must be whole numbers.';
        }

        return null;
    }

    public function index(Request $request)
    {
        $cart = $request->session()->get('cart', []);

        $products = Product::with('sellingUnits')
            ->whereIn('id', array_keys($cart))
            ->get()
            ->keyBy('id');

        $cartProductIds = array_map('intval', array_keys($cart));
        $hasRiceInCart = $products->contains(
            fn (Product $product) => !$this->isGeneric($product)
        );
        $suggestedItems = $cartProductIds === []
            ? collect()
            : $this->cartAddOnSuggestions($cartProductIds, $hasRiceInCart);

        $items = [];
        $total = 0.0;
        $needsPriceConfirmation = false;
        $hasUnavailableStock = false;

        foreach ($cart as $id => $item) {
            $product = $products->get($id);

            if (!$product || !$product->is_active) {
                $hasUnavailableStock = true;
                continue;
            }

            $unit = (string) ($item['unit'] ?? $this->productUnit($product));
            $quantity = (float) ($item['quantity'] ?? 1);

            $error = $this->validateCartQuantity(
                $product,
                $unit,
                $quantity
            );

            $price = $this->validPrice($product, $unit);
            $inStock = $this->stockAvailable($product, $unit, $quantity);

            $confirmed = $price !== null;
            $subtotal = $confirmed ? round($price * $quantity, 2) : null;

            if ($confirmed) {
                $total += $subtotal;
            } else {
                $needsPriceConfirmation = true;
            }

            if (!$inStock || $error !== null) {
                $hasUnavailableStock = true;
            }

            $sellingUnit = $this->isGeneric($product)
                ? $this->sellingUnit($product, $unit)
                : null;

            $items[] = [
                'product' => $product,
                'unit' => $unit,
                'quantity' => $quantity,
                'price' => $price,
                'subtotal' => $subtotal,
                'confirmed' => $confirmed,
                'requested_kg' => $this->quantityInKg(
                    $product,
                    $unit,
                    $quantity
                ),
                'requested_base_quantity' => $sellingUnit
                    ? round(
                        $quantity * (float) $sellingUnit->conversion_factor,
                        3
                    )
                    : null,
                'base_unit' => $product->base_unit,
                'in_stock' => $inStock,
                'minimum_met' => $error === null,
                'quantity_error' => $error,
                'is_generic' => $this->isGeneric($product),
                'selling_units' => $this->isGeneric($product)
                    ? $product->sellingUnits
                        ->where('is_active', true)
                        ->values()
                    : collect(),
            ];
        }

        $canProceed = count($items) > 0
            && !$needsPriceConfirmation
            && !$hasUnavailableStock;

        return view('cart.index', compact(
            'items',
            'total',
            'needsPriceConfirmation',
            'hasUnavailableStock',
            'canProceed',
            'suggestedItems'
        ));
    }

    private function cartAddOnSuggestions(array $cartProductIds, bool $hasRiceInCart)
    {
        $products = Product::query()
            ->where('is_active', true)
            ->whereNotIn('id', $cartProductIds)
            ->with(['sellingUnits' => fn ($query) => $query->where('is_active', true)])
            ->get();

        $suggestions = $products->map(function (Product $product) {
            if (!$product->price_confirmed) {
                return null;
            }

            if ($this->isGeneric($product)) {
                $stock = (float) $product->stock_base_quantity;
                $units = $product->sellingUnits
                    ->filter(fn (ProductSellingUnit $unit) =>
                        (float) $unit->conversion_factor > 0
                        && (float) $unit->selling_price > 0
                        && (float) $unit->conversion_factor <= $stock
                    );

                $baseUnit = strtolower(trim(
                    (string) ($product->base_unit ?: $product->stock_unit ?: 'piece')
                ));
                $unit = $units->first(fn (ProductSellingUnit $candidate) =>
                    strtolower($candidate->unit_name) === $baseUnit
                ) ?? $units->sortBy('conversion_factor')->first();

                if (!$unit) {
                    return null;
                }

                return [
                    'product' => $product,
                    'unit' => $unit->unit_name,
                    'quantity' => 1,
                    'price' => (float) $unit->selling_price,
                    'stock_text' => number_format(
                        $stock / (float) $unit->conversion_factor,
                        2
                    ) . ' ' . $unit->unit_name . ' available',
                    'is_generic' => true,
                ];
            }

            $price = (float) $product->price_per_kg;
            $stock = (float) $product->stock_kg;

            if ($price <= 0 || $stock < self::MINIMUM_KG_ORDER) {
                return null;
            }

            return [
                'product' => $product,
                'unit' => 'kg',
                'quantity' => self::MINIMUM_KG_ORDER,
                'price' => $price,
                'stock_text' => number_format($stock, 2) . ' kg available',
                'is_generic' => false,
            ];
        })->filter();

        return $suggestions
            ->sortBy(function (array $item) use ($hasRiceInCart): int {
                $product = $item['product'];
                $text = strtolower($product->name . ' ' . $product->category . ' ' . $product->description);
                $groceryScore = preg_match('/egg|oil|sugar|coffee|noodle|canned|grocery|sardine/', $text) ? 0 : 1;
                $genericScore = $item['is_generic'] ? 0 : 1;

                return $hasRiceInCart
                    ? ($genericScore * 10 + $groceryScore)
                    : ($groceryScore * 10 + $genericScore);
            })
            ->take(4)
            ->values();
    }

    public function add(Request $request, Product $product)
    {
        abort_unless($product->is_active, 404);

        $product->load('sellingUnits');

        $validated = $request->validate([
            'unit' => ['required', 'string', 'max:20'],
            'quantity' => [
                'required',
                'numeric',
                'gt:0',
                'max:' . self::MAXIMUM_QUANTITY,
            ],
        ]);

        $unit = (string) $validated['unit'];
        $quantity = (float) $validated['quantity'];

        $error = $this->validateCartQuantity(
            $product,
            $unit,
            $quantity
        );

        if ($error !== null) {
            return back()->with('cart_error', $error);
        }

        if (!$product->price_confirmed) {
            return back()->with(
                'cart_error',
                'The store has not confirmed this product price yet.'
            );
        }

        if ($this->validPrice($product, $unit) === null) {
            return back()->with(
                'cart_error',
                'This purchase option does not have a valid confirmed price.'
            );
        }

        $cart = $request->session()->get('cart', []);
        $id = (string) $product->id;

        if (
            isset($cart[$id])
            && ($cart[$id]['unit'] ?? '') === $unit
        ) {
            $quantity += (float) ($cart[$id]['quantity'] ?? 0);
        }

        $error = $this->validateCartQuantity(
            $product,
            $unit,
            $quantity
        );

        if ($error !== null) {
            return back()->with('cart_error', $error);
        }

        if (!$this->stockAvailable($product, $unit, $quantity)) {
            return back()->with(
                'cart_error',
                'Insufficient stock for the requested quantity.'
            );
        }

        $cart[$id] = [
            'unit' => $unit,
            'quantity' => $quantity,
        ];

        $request->session()->put('cart', $cart);

        return redirect()
            ->route('cart.index')
            ->with('cart_success', 'Product added to your cart.');
    }

    public function update(Request $request, Product $product)
    {
        abort_unless($product->is_active, 404);

        $product->load('sellingUnits');

        $validated = $request->validate([
            'unit' => ['required', 'string', 'max:20'],
            'quantity' => [
                'required',
                'numeric',
                'gt:0',
                'max:' . self::MAXIMUM_QUANTITY,
            ],
        ]);

        $id = (string) $product->id;
        $cart = $request->session()->get('cart', []);

        if (!isset($cart[$id])) {
            return redirect()->route('cart.index');
        }

        $unit = (string) $validated['unit'];
        $quantity = (float) $validated['quantity'];

        $error = $this->validateCartQuantity(
            $product,
            $unit,
            $quantity
        );

        if ($error !== null) {
            return back()->with('cart_error', $error);
        }

        if ($this->validPrice($product, $unit) === null) {
            return back()->with(
                'cart_error',
                'The store has not confirmed a valid price for this product.'
            );
        }

        if (!$this->stockAvailable($product, $unit, $quantity)) {
            return back()->with(
                'cart_error',
                'Insufficient stock for the requested quantity.'
            );
        }

        $cart[$id] = [
            'unit' => $unit,
            'quantity' => $quantity,
        ];

        $request->session()->put('cart', $cart);

        return redirect()
            ->route('cart.index')
            ->with('cart_success', 'Cart updated successfully.');
    }

    public function remove(Request $request, Product $product)
    {
        $cart = $request->session()->get('cart', []);

        unset($cart[(string) $product->id]);

        $request->session()->put('cart', $cart);

        return redirect()
            ->route('cart.index')
            ->with('cart_success', 'Product removed from your cart.');
    }
}
