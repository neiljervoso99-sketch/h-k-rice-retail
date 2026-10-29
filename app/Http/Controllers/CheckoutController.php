<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductSellingUnit;
use App\Support\OrderEmailNotifier;
use App\Support\UserAlertNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    private const BARANGAY_ZONES = [
        'Bito' => 'abuyog_proper',
        'Loyonsawang' => 'abuyog_proper',
        'Victory' => 'abuyog_proper',
        'Buntay' => 'abuyog_proper',
        'Nalibunan' => 'abuyog_proper',
        'Santa Fe' => 'abuyog_proper',
        'Santo Niño' => 'abuyog_proper',
        'Guintagbucan' => 'abuyog_proper',
        'Can-uguib' => 'abuyog_proper',
        'Barayong' => 'abuyog_proper',
        'Pili' => 'abuyog_proper',
        'Lacdas' => 'abuyog_proper',
        'Minis' => 'abuyog_proper',
        'Catoogan' => 'abuyog_proper',

        'Buenavista' => 'central_nearby',
        'Alangilan' => 'central_nearby',
        'Balocawe' => 'central_nearby',
        'Balocawehay' => 'central_nearby',
        'Bunga' => 'central_nearby',
        'Can-aporong' => 'central_nearby',
        'Canmarating' => 'central_nearby',
        'Capilian' => 'central_nearby',
        'Cadac-an' => 'central_nearby',

        'Buaya' => 'medium',
        'Katipunan' => 'medium',
        'Kikilo' => 'medium',
        'Laray' => 'medium',
        'Lawa-an' => 'medium',
        'Mag-atubang' => 'medium',
        'Mahayahay' => 'medium',
        'Maitum' => 'medium',
        'Odiongan' => 'medium',
        'Pagsang-an' => 'medium',
        'Tib-o' => 'medium',
        'Tinalian' => 'medium',
        'Tinocolan' => 'medium',
        'Santa Lucia' => 'medium',
        'Bulak' => 'medium',

        'Tabigue' => 'far',
        'Tadoc' => 'far',
        'Old Taligue' => 'far',
        'New Taligue' => 'far',
        'Paguite' => 'far',
        'Parasanon' => 'far',
        'Picas Sur' => 'far',
        'Pilar' => 'far',
        'Pinamanagan' => 'far',
        'Salvacion' => 'far',
        'San Francisco' => 'far',
        'San Isidro' => 'far',
        'San Roque' => 'far',
        'Malaguicay' => 'far',
        'Hampipila' => 'far',
        'Nebga' => 'far',
        'Anibongon' => 'far',
        'Burubud-an' => 'far',
        'Cagbolo' => 'far',
        'Bagacay' => 'far',
        'Bahay' => 'far',
        'Balinsasayao' => 'far',
        'Matagnao' => 'far',
        'Combis' => 'far',
        'Dingle' => 'far',
        'Libertad' => 'far',
        'Mahagna' => 'far',
        'Bayabas' => 'far',
        'Tuy-a' => 'far',
    ];

    public function getConfiguredBarangayZonesForLookup(): array
    {
        return self::BARANGAY_ZONES;
    }

    private function isGeneric(Product $product): bool
    {
        return ($product->inventory_type ?? 'rice') === 'generic';
    }

    private function unitFor(Product $product, array $cartItem): string
    {
        if ($this->isGeneric($product)) {
            return (string) ($cartItem['unit'] ?? $product->stock_unit ?? 'piece');
        }

        return ($cartItem['unit'] ?? 'kg') === 'sack' ? 'sack' : 'kg';
    }

    private function quantityFor(array $cartItem): ?float
    {
        $raw = $cartItem['quantity'] ?? null;

        if (!is_numeric($raw)) {
            return null;
        }

        $quantity = (float) $raw;

        if (!is_finite($quantity) || $quantity <= 0 || $quantity > 1000) {
            return null;
        }

        return round($quantity, 2);
    }

    private function priceFor(Product $product, string $unit): ?float
    {
        if (!(bool) $product->price_confirmed) {
            return null;
        }

        if ($this->isGeneric($product)) {
            $sellingUnit = ProductSellingUnit::query()
                ->where('product_id', $product->id)
                ->where('unit_name', $unit)
                ->where('is_active', true)
                ->first();

            $price = $sellingUnit?->selling_price;
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
        ) {
            return null;
        }

        return (float) $price;
    }

    private function prepareCartItem(
        Product $product,
        array $cartItem
    ): array {
        $generic = $this->isGeneric($product);
        $unit = strtolower(trim($this->unitFor($product, $cartItem)));
        $quantity = $this->quantityFor($cartItem);

        if ($quantity === null) {
            throw ValidationException::withMessages([
                'cart' => "Invalid quantity for {$product->name}. Please update your cart.",
            ]);
        }

        if ($generic) {
            $sellingUnit = ProductSellingUnit::query()
                ->where('product_id', $product->id)
                ->where('unit_name', $unit)
                ->where('is_active', true)
                ->first();

            if (!$sellingUnit) {
                throw ValidationException::withMessages([
                    'cart' => "The {$unit} option is unavailable for {$product->name}. Please update your cart.",
                ]);
            }

            $factor = (float) $sellingUnit->conversion_factor;

            if (!is_finite($factor) || $factor <= 0) {
                throw ValidationException::withMessages([
                    'cart' => "The stock conversion for {$product->name} is invalid.",
                ]);
            }

            $requestedBaseQuantity = round($quantity * $factor, 3);

            if (
                !is_finite($requestedBaseQuantity)
                || $requestedBaseQuantity <= 0
            ) {
                throw ValidationException::withMessages([
                    'cart' => "The requested quantity for {$product->name} is invalid.",
                ]);
            }

            $availableBaseQuantity = (float) $product->stock_base_quantity;

            $quantityKg = null;
            $inStock = is_finite($availableBaseQuantity)
                && $availableBaseQuantity > 0
                && $requestedBaseQuantity <= $availableBaseQuantity;
            $minimumMet = true;
        } else {
            if (!in_array($unit, ['kg', 'sack'], true)) {
                throw ValidationException::withMessages([
                    'cart' => "Invalid rice purchase unit for {$product->name}.",
                ]);
            }

            if ($unit === 'kg' && $quantity < 5) {
                throw ValidationException::withMessages([
                    'cart' => 'Minimum order is 5 kg per kilogram rice purchase.',
                ]);
            }

            if (floor($quantity) !== $quantity) {
                throw ValidationException::withMessages([
                    'cart' => 'Rice quantities must be whole numbers.',
                ]);
            }

            $sackWeight = (float) $product->sack_weight_kg;

            if ($unit === 'sack' && $sackWeight < 5) {
                throw ValidationException::withMessages([
                    'cart' => "A valid sack weight is required for {$product->name}.",
                ]);
            }

            $quantityKg = $unit === 'kg'
                ? $quantity
                : $quantity * $sackWeight;

            $inStock = (float) $product->stock_kg >= $quantityKg
                && (float) $product->stock_kg > 0;

            $minimumMet = $unit === 'sack' || $quantity >= 5;

            $requestedBaseQuantity = null;
        }

        $price = $this->priceFor($product, $unit);

        return [
            'product' => $product,
            'unit' => $unit,
            'quantity' => $quantity,
            'price' => $price,
            'subtotal' => $price !== null
                ? round($price * $quantity, 2)
                : null,
            'confirmed' => $price !== null,
            'requested_kg' => $quantityKg,
            'requested_base_quantity' => $requestedBaseQuantity,
            'minimum_met' => $minimumMet,
            'in_stock' => $inStock,
            'is_generic' => $generic,
        ];
    }

    private function calculateDeliveryFee(
        string $method,
        ?string $area,
        int $sackCount,
        bool $hasGenericItems = false
    ): float {
        if ($method === 'pickup') {
            return 0.00;
        }

        if ($method !== 'delivery') {
            throw ValidationException::withMessages([
                'delivery_method' => 'Please select a valid fulfilment method.',
            ]);
        }

        if ($area === 'abuyog_proper') {
            return $sackCount <= 2 ? 50.00 : 100.00;
        }

        if (in_array($area, ['central_nearby', 'medium', 'far'], true)) {
            if ($sackCount < 1 && !$hasGenericItems) {
                throw ValidationException::withMessages([
                    'cart' => 'Delivery to this area requires at least one sack of rice.',
                ]);
            }

            return $sackCount >= 6 ? 500.00 : 250.00;
        }

        throw ValidationException::withMessages([
            'barangay' => 'Please select a valid delivery barangay.',
        ]);
    }

    public function create(Request $request)
    {
        $cart = $request->session()->get('cart', []);

        if (empty($cart)) {
            return redirect()
                ->route('cart.index')
                ->with('cart_error', 'Your cart is empty.');
        }

        $products = Product::whereIn('id', array_keys($cart))
            ->get()
            ->keyBy('id');

        $items = [];
        $subtotal = 0.0;
        $totalSacks = 0;
        $needsPriceConfirmation = false;
        $hasUnavailableStock = false;

        foreach ($cart as $id => $cartItem) {
            $product = $products->get($id);

            if (!$product || !$product->is_active) {
                $hasUnavailableStock = true;
                continue;
            }

            try {
                $item = $this->prepareCartItem($product, $cartItem);
            } catch (ValidationException $e) {
                $hasUnavailableStock = true;
                continue;
            }

            if (!$item['confirmed']) {
                $needsPriceConfirmation = true;
            } else {
                $subtotal += $item['subtotal'];
            }

            if (!$item['minimum_met'] || !$item['in_stock']) {
                $hasUnavailableStock = true;
            }

            if (!$item['is_generic'] && $item['unit'] === 'sack') {
                $totalSacks += (int) $item['quantity'];
            }

            $items[] = $item;
        }

        $subtotal = round($subtotal, 2);
        $canProceed = count($items) > 0
            && !$needsPriceConfirmation
            && !$hasUnavailableStock;

        $barangayZones = self::BARANGAY_ZONES;

        return view('checkout.create', compact(
            'items',
            'subtotal',
            'totalSacks',
            'needsPriceConfirmation',
            'hasUnavailableStock',
            'canProceed',
            'barangayZones'
        ));
    }

    public function confirmation(Request $request, Order $order)
    {
        abort_unless(
            (int) $order->user_id === (int) $request->user()->id,
            404
        );

        $order->load(['items', 'payments']);

        return view('checkout.confirmation', compact('order'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'recipient_name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'max:30'],
            'delivery_method' => ['required', Rule::in(['delivery', 'pickup'])],
            'barangay' => [
                'required_if:delivery_method,delivery',
                'nullable',
                'string',
                'max:120',
                Rule::in(array_keys(self::BARANGAY_ZONES)),
            ],
            'address_line' => [
                'required_if:delivery_method,delivery',
                'nullable',
                'string',
                'max:255',
            ],
            'municipality' => ['required', 'string', 'max:120'],
            'province' => ['required', 'string', 'max:120'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'payment_method' => ['required', Rule::in(['cash', 'gcash'])],
            'gcash_reference_number' => [
                'required_if:payment_method,gcash',
                'nullable',
                'string',
                'max:100',
            ],
            'payment_proof' => [
                'required_if:payment_method,gcash',
                'nullable',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
            'receipt_requested' => ['nullable', 'boolean'],
            'customer_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $cart = $request->session()->get('cart', []);

        if (empty($cart)) {
            return redirect()
                ->route('cart.index')
                ->with('cart_error', 'Your cart is empty.');
        }

        $order = DB::transaction(function () use ($request, $validated, $cart) {
            $subtotal = 0.0;
            $totalSacks = 0;
            $hasGenericItems = false;
            $preparedItems = [];

            $products = Product::whereIn('id', array_keys($cart))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($cart as $id => $cartItem) {
                $product = $products->get($id);

                if (!$product || !$product->is_active) {
                    throw ValidationException::withMessages([
                        'cart' => 'A product in your cart is unavailable. Please review your cart.',
                    ]);
                }

                $item = $this->prepareCartItem($product, $cartItem);

                if (!$item['confirmed']) {
                    throw ValidationException::withMessages([
                        'cart' => "The price for {$product->name} is not confirmed. Please review your cart.",
                    ]);
                }

                if (!$item['in_stock']) {
                    throw ValidationException::withMessages([
                        'cart' => "Insufficient stock for {$product->name}. Please update your cart.",
                    ]);
                }

                if (!$item['minimum_met']) {
                    throw ValidationException::withMessages([
                        'cart' => "The minimum quantity for {$product->name} has not been met.",
                    ]);
                }

                if ($item['is_generic']) {
                    $hasGenericItems = true;
                } elseif ($item['unit'] === 'sack') {
                    $totalSacks += (int) $item['quantity'];
                }

                $subtotal += $item['subtotal'];
                $preparedItems[] = $item;
            }

            $deliveryMethod = $validated['delivery_method'];
            $barangay = $deliveryMethod === 'delivery'
                ? $validated['barangay']
                : null;
            $deliveryArea = $deliveryMethod === 'delivery'
                ? self::BARANGAY_ZONES[$barangay]
                : null;

            $deliveryFee = $this->calculateDeliveryFee(
                $deliveryMethod,
                $deliveryArea,
                $totalSacks,
                $hasGenericItems
            );

            $subtotal = round($subtotal, 2);
            $total = round($subtotal + $deliveryFee, 2);

            $order = Order::create([
                'order_number' => 'HK-' . now()->format('Ymd') . '-'
                    . Str::upper(Str::random(10)),
                'user_id' => $request->user()->id,
                'recipient_name' => $validated['recipient_name'],
                'phone' => $validated['phone'],
                'address_line' => $deliveryMethod === 'delivery'
                    ? $validated['address_line']
                    : null,
                'barangay' => $barangay,
                'municipality' => $validated['municipality'],
                'province' => $validated['province'],
                'postal_code' => $validated['postal_code'] ?? null,
                'delivery_method' => $deliveryMethod,
                'delivery_area' => $deliveryArea,
                'delivery_latitude' => null,
                'delivery_longitude' => null,
                'payment_method' => $validated['payment_method'],
                'payment_status' => 'pending',
                'status' => 'pending',
                'subtotal' => $subtotal,
                'delivery_fee' => $deliveryFee,
                'total' => $total,
                'receipt_requested' => $request->boolean('receipt_requested'),
                'customer_notes' => $validated['customer_notes'] ?? null,
            ]);

            foreach ($preparedItems as $item) {
                $product = $item['product'];
                $generic = $item['is_generic'];

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'brand' => $product->brand,
                    'unit_type' => $item['unit'],
                    'quantity_unit' => $item['unit'],
                    'quantity' => $item['quantity'],
                    'sack_weight_kg' => !$generic && $item['unit'] === 'sack'
                        ? (int) $product->sack_weight_kg
                        : null,
                    'quantity_kg' => $generic
                        ? null
                        : $item['requested_kg'],
                    'unit_price' => $item['price'],
                    'line_total' => $item['subtotal'],
                    'unit_cost' => null,
                ]);
            }

            $proofPath = null;

            if ($validated['payment_method'] === 'gcash') {
                $proofPath = $request->file('payment_proof')
                    ->store('payment-proofs', 'local');

                if ($proofPath === false) {
                    throw ValidationException::withMessages([
                        'payment_proof' => 'The payment screenshot could not be saved. Please try again.',
                    ]);
                }
            }

            Payment::create([
                'order_id' => $order->id,
                'method' => $validated['payment_method'],
                'amount' => $total,
                'status' => 'pending',
                'reference_number' => $validated['payment_method'] === 'gcash'
                    ? $validated['gcash_reference_number']
                    : null,
                'proof_path' => $proofPath,
            ]);

            return $order;
        }, 3);

        $request->session()->forget('cart');

        OrderEmailNotifier::send(
            $order->fresh(),
            'placed'
        );

        UserAlertNotifier::admins(
            'New customer order',
            'Order ' . $order->order_number . ' has been placed.',
            route('admin.orders.show', $order),
        );

        return redirect()->route('checkout.confirmation', [
            'order' => $order->id,
        ]);
    }
}
