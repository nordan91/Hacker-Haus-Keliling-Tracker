<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Seed the products table with Haus beverages.
     */
    public function run(): void
    {
        $products = [
            // Teh
            ['name' => 'Jasmine Tea',         'category' => 'Teh',     'price' => 6000,  'is_active' => true],
            ['name' => 'Thai Tea',            'category' => 'Teh',     'price' => 10000, 'is_active' => true],
            ['name' => 'Green Thai Tea',      'category' => 'Teh',     'price' => 10000, 'is_active' => true],
            ['name' => 'Lemon Tea',           'category' => 'Teh',     'price' => 8000,  'is_active' => false], // Tidak produksi lagi

            // Matcha
            ['name' => 'Matcha Latte',        'category' => 'Matcha',  'price' => 12000, 'is_active' => true],

            // Coklat
            ['name' => 'Chocolate Dubai',     'category' => 'Coklat',  'price' => 12000, 'is_active' => true],
            ['name' => 'Drink Beng-Beng',     'category' => 'Coklat',  'price' => 12000, 'is_active' => true],

            // Kopi
            ['name' => 'Kopi Butterscotch',   'category' => 'Kopi',    'price' => 10000, 'is_active' => true],
            ['name' => 'Kopi Susu Gula Aren', 'category' => 'Kopi',    'price' => 8000,  'is_active' => true],

            // Lainnya (Discontinued)
            ['name' => 'Taro',                'category' => 'Lainnya', 'price' => 14000, 'is_active' => false], // Tidak produksi lagi
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}

