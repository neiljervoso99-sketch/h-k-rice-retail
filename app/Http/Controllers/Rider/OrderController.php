<?php

namespace App\Http\Controllers\Rider;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Support\UserAlertNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    /**
     * Display deliveries assigned to the logged-in rider.
     */
    public function index()
    {
        $riderId = auth()->id();

        $baseQuery = Order::where('rider_id', $riderId);

        $counts = [
            'total' => (clone $baseQuery)->count(),
            'active' => (clone $baseQuery)
                ->where('status', 'accepted')
                ->count(),
            'completed' => (clone $baseQuery)
                ->where('status', 'completed')
                ->count(),
        ];

        $orders = Order::with('customer')
            ->where('rider_id', $riderId)
            ->latest()
            ->paginate(10);

        return view('rider.orders.index', compact('orders', 'counts'));
    }

    /**
     * Display an assigned delivery.
     */
    public function show(Order $order)
    {
        $this->authorizeAssignedOrder($order);

        $order->load(['customer', 'items']);

        return view('rider.orders.show', compact('order'));
    }

    /**
     * Upload delivery proof for an accepted assigned order.
     */
    public function uploadProof(Request $request, Order $order)
    {
        $this->authorizeAssignedOrder($order);

        if ($order->status !== 'accepted') {
            return back()->withErrors([
                'proof' => 'Only accepted deliveries can receive delivery proof.',
            ]);
        }

        $validated = $request->validate([
            'proof' => [
                'required',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ]);

        $file = $validated['proof'];

        // Store privately; do not expose the file through a public URL.
        $path = $file->store('delivery-proofs', 'local');

        if (!$path) {
            return back()->withErrors([
                'proof' => 'The delivery photo could not be saved. Please try again.',
            ]);
        }

        // Remove the previous proof only after the new proof has been stored.
        $oldPath = $order->delivery_proof_path;

        $order->delivery_proof_path = $path;
        $order->delivery_proof_uploaded_at = now();
        $order->save();

        if ($oldPath && $oldPath !== $path) {
            Storage::disk('local')->delete($oldPath);
        }

        return redirect()
            ->route('rider.orders.show', $order)
            ->with('success', 'Delivery proof uploaded successfully.');
    }

    /**
     * Display the private delivery proof to the assigned rider.
     */
    public function viewProof(Order $order)
    {
        $this->authorizeAssignedOrder($order);

        if (!$order->delivery_proof_path) {
            abort(404, 'Delivery proof has not been uploaded.');
        }

        $disk = Storage::disk('local');
        $path = $order->delivery_proof_path;

        if (!$disk->exists($path)) {
            abort(404, 'Delivery proof file was not found.');
        }

        $mimeType = $disk->mimeType($path);

        if (!in_array($mimeType, [
            'image/jpeg',
            'image/png',
            'image/webp',
        ], true)) {
            abort(404, 'Unsupported delivery proof file.');
        }

        return response()->file($disk->path($path), [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
            'Content-Disposition' => 'inline',
        ]);
    }

    /**
     * Complete a delivery only after proof has been uploaded.
     */
    public function complete(Order $order)
    {
        $this->authorizeAssignedOrder($order);

        if ($order->status !== 'accepted') {
            return back()->withErrors([
                'delivery' => 'Only accepted deliveries can be completed.',
            ]);
        }

        if (!$order->inventory_deducted_at) {
            return back()->withErrors([
                'delivery' => 'This order is not ready for delivery completion.',
            ]);
        }

        if (!$order->delivery_proof_path ||
            !Storage::disk('local')->exists($order->delivery_proof_path)) {
            return back()->withErrors([
                'delivery' => 'Upload a valid delivery proof before completing this order.',
            ]);
        }

        $order->status = 'completed';
        $order->completed_at = now();
        $order->delivered_at = $order->completed_at;
        $order->save();

        $order->loadMissing('customer');
        if ($order->customer) {
            UserAlertNotifier::send(
                $order->customer,
                'Delivery completed',
                'Your order ' . $order->order_number . ' has been delivered.',
                route('customer.orders.show', $order),
            );
        }

        return redirect()
            ->route('rider.orders.show', $order)
            ->with('success', 'Delivery marked as completed.');
    }

    /**
     * Prevent riders from accessing another rider's order.
     */
    private function authorizeAssignedOrder(Order $order): void
    {
        abort_unless(
            (int) $order->rider_id === (int) auth()->id(),
            403,
            'You are not authorized to access this delivery.'
        );
    }
}
