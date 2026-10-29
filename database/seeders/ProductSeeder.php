<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name' => 'Magdalena Local Rice',
                'rice_type' => 'Local Rice',
                'price_per_kg' => 50,
                'price_per_sack' => 2400,
                'sack_weight_kg' => null,
            ],
            [
                'name' => 'Carlos Uno Imported',
                'rice_type' => 'Imported Rice',
                'price_per_kg' => 49,
                'price_per_sack' => 2400,
                'sack_weight_kg' => null,
            ],
            [
                'name' => 'Carlos Uno Local',
                'rice_type' => 'Local Rice',
                'price_per_kg' => 52,
                'price_per_sack' => 2500,
                'sack_weight_kg' => null,
            ],
            [
                'name' => 'Senior Roberto 50 kg',
                'rice_type' => 'Local Rice',
                'price_per_kg' => 51,
                'price_per_sack' => 2450,
                'sack_weight_kg' => 50,
            ],
            [
                'name' => 'Senior Roberto 25 kg',
                'rice_type' => 'Local Rice',
                'price_per_kg' => null,
                'price_per_sack' => 1275,
                'sack_weight_kg' => 25,
            ],
            [
                'name' => 'Marina',
                'rice_type' => 'Rice',
                'price_per_kg' => 56,
                'price_per_sack' => 2600,
                'sack_weight_kg' => null,
            ],
            [
                'name' => 'Mars',
                'rice_type' => 'Rice',
                'price_per_kg' => 53,
                'price_per_sack' => 2500,
                'sack_weight_kg' => null,
            ],
            [
                'name' => 'Don Ramon',
                'rice_type' => 'Rice',
                'price_per_kg' => 52,
                'price_per_sack' => 2400,
                'sack_weight_kg' => null,
            ],
            [
                'name' => 'Carlos Imported 25 kg',
                'rice_type' => 'Imported Rice',
                'price_per_kg' => null,
                'price_per_sack' => 1225,
                'sack_weight_kg' => 25,
            ],
            [
                'name' => 'Red Rice 50 kg',
                'rice_type' => 'Red Rice',
                'price_per_kg' => 63,
                'price_per_sack' => 2950,
                'sack_weight_kg' => 50,
            ],
            [
                'name' => 'N&N Local rice',
                'rice_type' => 'Local Rice',
                'price_per_kg' => 50,
                'price_per_sack' => 2400,
                'sack_weight_kg' => null,
            ],
            [
                'name' => 'Japonica 50 kg',
                'rice_type' => 'Japonica',
                'price_per_kg' => null,
                'price_per_sack' => null,
                'sack_weight_kg' => 50,
            ],
            [
                'name' => 'Japonica 25 kg',
                'rice_type' => 'Japonica',
                'price_per_kg' => null,
                'price_per_sack' => null,
                'sack_weight_kg' => 25,
            ],
            [
                'name' => 'Pilit',
                'rice_type' => 'Specialty Rice',
                'price_per_kg' => 60,
                'price_per_sack' => null,
                'sack_weight_kg' => null,
            ],
            [
                'name' => 'Jasmine 5 kg',
                'rice_type' => 'Jasmine Rice',
                'price_per_kg' => null,
                'price_per_sack' => null,
                'sack_weight_kg' => 5,
                'description' => 'Preliminary note: ₱290 per pack; confirm pricing unit.',
            ],
            [
                'name' => 'Jasmine 10 kg',
                'rice_type' => 'Jasmine Rice',
                'price_per_kg' => null,
                'price_per_sack' => null,
                'sack_weight_kg' => 10,
                'description' => 'Preliminary note: ₱573 per pack; confirm pricing unit.',
            ],
        ];

        foreach ($products as $data) {
            Product::updateOrCreate(
                ['name' => $data['name']],
                array_merge([
                    'brand' => null,
                    'description' => null,
                    'stock_kg' => 0,
                    'low_stock_threshold' => 20,
                    'image' => null,
                    'is_active' => false,
                ], $data)
            );
        }
    }
}
