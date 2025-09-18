<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Apparel',
                'description' => 'Clothing and wearable items',
                'slug' => 'apparel',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'T-Shirts',
                'description' => 'Comfortable cotton t-shirts in various styles',
                'slug' => 't-shirts',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Hoodies & Sweatshirts',
                'description' => 'Warm and cozy hoodies and sweatshirts',
                'slug' => 'hoodies-sweatshirts',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Accessories',
                'description' => 'Fashion accessories and add-ons',
                'slug' => 'accessories',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Hats & Caps',
                'description' => 'Stylish headwear including baseball caps and beanies',
                'slug' => 'hats-caps',
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'Bags & Backpacks',
                'description' => 'Practical bags and backpacks for everyday use',
                'slug' => 'bags-backpacks',
                'is_active' => true,
                'sort_order' => 6,
            ],
            [
                'name' => 'Stickers & Decals',
                'description' => 'Custom stickers and decals',
                'slug' => 'stickers-decals',
                'is_active' => true,
                'sort_order' => 7,
            ],
            [
                'name' => 'Drinkware',
                'description' => 'Mugs, bottles, and drinking accessories',
                'slug' => 'drinkware',
                'is_active' => true,
                'sort_order' => 8,
            ],
            [
                'name' => 'Mugs & Cups',
                'description' => 'Coffee mugs and drinking cups',
                'slug' => 'mugs-cups',
                'is_active' => true,
                'sort_order' => 9,
            ],
            [
                'name' => 'Water Bottles',
                'description' => 'Reusable water bottles and tumblers',
                'slug' => 'water-bottles',
                'is_active' => true,
                'sort_order' => 10,
            ],
        ];

        foreach ($categories as $category) {
            \App\Models\Category::create($category);
        }
    }
}
