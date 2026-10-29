<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'name',
        'brand',
        'rice_type',
        'category',
        'variant_size',
        'variant_color',
        'description',
        'price_per_kg',
        'price_per_sack',
        'sack_weight_kg',
        'stock_kg',
        'low_stock_threshold',
        'image',
        'is_active',
        'price_confirmed',
        'cost_per_sack',
        'profit_percentage',
        'qr_code',
        'inventory_type',
        'stock_unit',
        'stock_quantity',
        'price_per_unit',
        'cost_per_unit',
        'low_stock_threshold_quantity',
        'base_unit',
        'stock_base_quantity',
        'unit_conversion_factor',
    ];

    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            $product->profit_percentage ??= 15;
        });
    }

    protected function casts(): array
    {
        return [
            'price_per_kg' => 'decimal:2',
            'price_per_sack' => 'decimal:2',
            'cost_per_sack' => 'decimal:2',
            'profit_percentage' => 'decimal:2',
            'stock_kg' => 'decimal:2',
            'stock_quantity' => 'decimal:2',
            'stock_base_quantity' => 'decimal:3',
            'unit_conversion_factor' => 'decimal:3',
            'price_per_unit' => 'decimal:2',
            'low_stock_threshold' => 'decimal:2',
            'low_stock_threshold_quantity' => 'decimal:2',
            'sack_weight_kg' => 'integer',
            'is_active' => 'boolean',
            'price_confirmed' => 'boolean',
        ];
    }

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function sellingUnits(): HasMany
    {
        return $this->hasMany(ProductSellingUnit::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }
}
