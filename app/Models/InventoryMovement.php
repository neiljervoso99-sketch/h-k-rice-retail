<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMovement extends Model
{
    protected $fillable = [
        'product_id',
        'product_name',
        'movement_type',
        'quantity_kg',
        'sacks',
        'quantity',
        'unit',
        'movement_date',
        'notes',
        'created_by',
        'delivery_reference',
        'purchase_order_receipt_item_id',
    ];

    protected function casts(): array
    {
        return [
            'quantity_kg' => 'decimal:2',
            'sacks' => 'decimal:2',
            'quantity' => 'decimal:2',
            'movement_date' => 'date',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function purchaseOrderReceiptItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderReceiptItem::class);
    }
}
