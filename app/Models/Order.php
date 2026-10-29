<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'order_number',
        'user_id',
        'recipient_name',
        'phone',
        'address_line',
        'barangay',
        'municipality',
        'province',
        'postal_code',
        'delivery_method',
        'delivery_area',
        'delivery_latitude',
        'delivery_longitude',
        'payment_method',
        'payment_status',
        'status',
        'subtotal',
        'delivery_fee',
        'total',
        'receipt_requested',
        'receipt_file',
        'customer_notes',
        'admin_notes',
        'accepted_at',
        'completed_at',
        'processing_at',
        'shipped_at',
        'delivered_at',
        'cancelled_at',
        'rider_id',
        'assigned_by',
        'assigned_at',
        'cash_collected_amount',
        'cash_collected_at',
        'delivery_notes',
        'inventory_deducted_at',
        'delivery_proof_path',
'delivery_proof_uploaded_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'total' => 'decimal:2',
            'cash_collected_amount' => 'decimal:2',
            'delivery_latitude' => 'decimal:7',
            'delivery_longitude' => 'decimal:7',
            'receipt_requested' => 'boolean',
            'accepted_at' => 'datetime',
            'completed_at' => 'datetime',
            'processing_at' => 'datetime',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'assigned_at' => 'datetime',
            'cash_collected_at' => 'datetime',
            'inventory_deducted_at' => 'datetime',
            'delivery_proof_uploaded_at' => 'datetime',
        ];
    }

    public function getDisplayStatusAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Pending',
            'accepted' => $this->shipped_at ? 'Shipped' : 'Processing',
            'completed' => 'Delivered',
            'cancelled' => 'Cancelled',
            default => str($this->status)->replace('_', ' ')->title()->toString(),
        };
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function rider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rider_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
