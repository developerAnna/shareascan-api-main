<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            // T-Shirts
            [
                'name' => 'Classic Cotton T-Shirt',
                'description' => 'Premium 100% cotton t-shirt with a comfortable regular fit. Perfect for everyday wear.',
                'sku' => 'TSH-001',
                'price' => 24.99,
                'compare_at_price' => 29.99,
                'category_id' => 2, // T-Shirts
                'stock_quantity' => 100,
                'image_url' => 'https://via.placeholder.com/600x600/2563eb/ffffff?text=Classic+T-Shirt',
                'thumbnail_url' => 'https://via.placeholder.com/300x300/2563eb/ffffff?text=Classic+T-Shirt',
                'status' => true,
                'featured' => true,
                'tags' => ['cotton', 'casual', 'comfortable'],
                'weight' => 0.2,
                'dimensions' => 'Various sizes',
                'meta_description' => 'Premium cotton t-shirt for everyday comfort',
            ],
            [
                'name' => 'Vintage Logo T-Shirt',
                'description' => 'Soft vintage-style t-shirt featuring our retro logo design. Made from premium tri-blend fabric.',
                'sku' => 'TSH-002',
                'price' => 29.99,
                'category_id' => 2, // T-Shirts
                'stock_quantity' => 75,
                'image_url' => 'https://via.placeholder.com/600x600/059669/ffffff?text=Vintage+Logo',
                'thumbnail_url' => 'https://via.placeholder.com/300x300/059669/ffffff?text=Vintage+Logo',
                'status' => true,
                'tags' => ['vintage', 'logo', 'tri-blend'],
                'weight' => 0.18,
                'dimensions' => 'Various sizes',
                'meta_description' => 'Vintage-style logo t-shirt with premium tri-blend fabric',
            ],
            [
                'name' => 'Performance Athletic T-Shirt',
                'description' => 'Moisture-wicking athletic t-shirt perfect for workouts and active lifestyle. Quick-dry technology.',
                'sku' => 'TSH-003',
                'price' => 34.99,
                'category_id' => 2, // T-Shirts
                'stock_quantity' => 60,
                'image_url' => 'https://via.placeholder.com/600x600/dc2626/ffffff?text=Athletic+Tee',
                'thumbnail_url' => 'https://via.placeholder.com/300x300/dc2626/ffffff?text=Athletic+Tee',
                'status' => true,
                'tags' => ['athletic', 'moisture-wicking', 'performance'],
                'weight' => 0.15,
                'dimensions' => 'Various sizes',
                'meta_description' => 'High-performance athletic t-shirt with moisture-wicking technology',
            ],

            // Hoodies & Sweatshirts
            [
                'name' => 'Premium Pullover Hoodie',
                'description' => 'Ultra-soft fleece hoodie with kangaroo pocket and adjustable drawstring hood. Perfect for layering.',
                'sku' => 'HOD-001',
                'price' => 59.99,
                'compare_at_price' => 69.99,
                'category_id' => 3, // Hoodies & Sweatshirts
                'stock_quantity' => 45,
                'image_url' => 'https://via.placeholder.com/600x600/374151/ffffff?text=Premium+Hoodie',
                'thumbnail_url' => 'https://via.placeholder.com/300x300/374151/ffffff?text=Premium+Hoodie',
                'status' => true,
                'featured' => true,
                'tags' => ['hoodie', 'fleece', 'comfortable'],
                'weight' => 0.8,
                'dimensions' => 'Various sizes',
                'meta_description' => 'Premium fleece hoodie with kangaroo pocket and adjustable hood',
            ],
            [
                'name' => 'Zip-Up Sweatshirt',
                'description' => 'Classic zip-up sweatshirt with ribbed cuffs and hem. Made from heavyweight cotton blend.',
                'sku' => 'SWT-001',
                'price' => 54.99,
                'category_id' => 3, // Hoodies & Sweatshirts
                'stock_quantity' => 35,
                'image_url' => 'https://via.placeholder.com/600x600/1f2937/ffffff?text=Zip+Sweatshirt',
                'thumbnail_url' => 'https://via.placeholder.com/300x300/1f2937/ffffff?text=Zip+Sweatshirt',
                'status' => true,
                'tags' => ['sweatshirt', 'zip-up', 'cotton-blend'],
                'weight' => 0.75,
                'dimensions' => 'Various sizes',
                'meta_description' => 'Classic zip-up sweatshirt made from heavyweight cotton blend',
            ],

            // Hats & Caps
            [
                'name' => 'Snapback Baseball Cap',
                'description' => 'Classic 6-panel snapback cap with flat brim and adjustable snap closure. Embroidered logo.',
                'sku' => 'HAT-001',
                'price' => 24.99,
                'category_id' => 5, // Hats & Caps
                'stock_quantity' => 80,
                'image_url' => 'https://via.placeholder.com/600x600/1e40af/ffffff?text=Snapback+Cap',
                'thumbnail_url' => 'https://via.placeholder.com/300x300/1e40af/ffffff?text=Snapback+Cap',
                'status' => true,
                'featured' => true,
                'tags' => ['snapback', 'baseball-cap', 'embroidered'],
                'weight' => 0.1,
                'dimensions' => 'One size fits most',
                'meta_description' => 'Classic snapback baseball cap with embroidered logo',
            ],
            [
                'name' => 'Knit Beanie',
                'description' => 'Warm and cozy knit beanie perfect for cold weather. Soft acrylic blend with cuffed design.',
                'sku' => 'HAT-002',
                'price' => 19.99,
                'category_id' => 5, // Hats & Caps
                'stock_quantity' => 65,
                'image_url' => 'https://via.placeholder.com/600x600/7c3aed/ffffff?text=Knit+Beanie',
                'thumbnail_url' => 'https://via.placeholder.com/300x300/7c3aed/ffffff?text=Knit+Beanie',
                'status' => true,
                'tags' => ['beanie', 'knit', 'winter'],
                'weight' => 0.08,
                'dimensions' => 'One size fits most',
                'meta_description' => 'Warm knit beanie perfect for cold weather',
            ],

            // Bags & Backpacks
            [
                'name' => 'Canvas Tote Bag',
                'description' => 'Durable canvas tote bag with reinforced handles. Perfect for shopping, beach, or everyday use.',
                'sku' => 'BAG-001',
                'price' => 29.99,
                'category_id' => 6, // Bags & Backpacks
                'stock_quantity' => 50,
                'image_url' => 'https://via.placeholder.com/600x600/ea580c/ffffff?text=Canvas+Tote',
                'thumbnail_url' => 'https://via.placeholder.com/300x300/ea580c/ffffff?text=Canvas+Tote',
                'status' => true,
                'tags' => ['tote', 'canvas', 'eco-friendly'],
                'weight' => 0.3,
                'dimensions' => '15" x 16" x 6"',
                'meta_description' => 'Durable canvas tote bag with reinforced handles',
            ],
            [
                'name' => 'Laptop Backpack',
                'description' => 'Professional laptop backpack with padded compartment for 15" laptops. Multiple pockets for organization.',
                'sku' => 'BAG-002',
                'price' => 79.99,
                'compare_at_price' => 89.99,
                'category_id' => 6, // Bags & Backpacks
                'stock_quantity' => 25,
                'image_url' => 'https://via.placeholder.com/600x600/0f172a/ffffff?text=Laptop+Backpack',
                'thumbnail_url' => 'https://via.placeholder.com/300x300/0f172a/ffffff?text=Laptop+Backpack',
                'status' => true,
                'featured' => true,
                'tags' => ['backpack', 'laptop', 'professional'],
                'weight' => 1.2,
                'dimensions' => '12" x 18" x 8"',
                'meta_description' => 'Professional laptop backpack with padded 15" compartment',
            ],

            // Stickers & Decals
            [
                'name' => 'Logo Sticker Pack',
                'description' => 'Set of 10 vinyl stickers featuring various logo designs. Waterproof and fade-resistant.',
                'sku' => 'STK-001',
                'price' => 9.99,
                'category_id' => 7, // Stickers & Decals
                'stock_quantity' => 200,
                'image_url' => 'https://via.placeholder.com/600x600/10b981/ffffff?text=Sticker+Pack',
                'thumbnail_url' => 'https://via.placeholder.com/300x300/10b981/ffffff?text=Sticker+Pack',
                'status' => true,
                'tags' => ['stickers', 'vinyl', 'waterproof'],
                'weight' => 0.02,
                'dimensions' => 'Various sizes (2"-4")',
                'meta_description' => 'Set of 10 vinyl logo stickers, waterproof and fade-resistant',
            ],

            // Mugs & Cups
            [
                'name' => 'Ceramic Coffee Mug',
                'description' => '11oz ceramic mug perfect for your morning coffee or tea. Dishwasher and microwave safe.',
                'sku' => 'MUG-001',
                'price' => 16.99,
                'category_id' => 9, // Mugs & Cups
                'stock_quantity' => 90,
                'image_url' => 'https://via.placeholder.com/600x600/dc2626/ffffff?text=Coffee+Mug',
                'thumbnail_url' => 'https://via.placeholder.com/300x300/dc2626/ffffff?text=Coffee+Mug',
                'status' => true,
                'tags' => ['mug', 'ceramic', 'dishwasher-safe'],
                'weight' => 0.4,
                'dimensions' => '3.8" x 3.8" x 4.3"',
                'meta_description' => '11oz ceramic coffee mug, dishwasher and microwave safe',
            ],
            [
                'name' => 'Travel Coffee Tumbler',
                'description' => 'Insulated stainless steel travel tumbler keeps drinks hot or cold for hours. Leak-proof lid included.',
                'sku' => 'MUG-002',
                'price' => 24.99,
                'category_id' => 9, // Mugs & Cups
                'stock_quantity' => 40,
                'image_url' => 'https://via.placeholder.com/600x600/475569/ffffff?text=Travel+Tumbler',
                'thumbnail_url' => 'https://via.placeholder.com/300x300/475569/ffffff?text=Travel+Tumbler',
                'status' => true,
                'featured' => true,
                'tags' => ['tumbler', 'insulated', 'travel'],
                'weight' => 0.35,
                'dimensions' => '3.5" x 3.5" x 7"',
                'meta_description' => 'Insulated stainless steel travel tumbler with leak-proof lid',
            ],

            // Water Bottles
            [
                'name' => 'Stainless Steel Water Bottle',
                'description' => '32oz double-wall vacuum insulated water bottle. Keeps cold for 24hrs, hot for 12hrs.',
                'sku' => 'BTL-001',
                'price' => 34.99,
                'category_id' => 10, // Water Bottles
                'stock_quantity' => 55,
                'image_url' => 'https://via.placeholder.com/600x600/0891b2/ffffff?text=Water+Bottle',
                'thumbnail_url' => 'https://via.placeholder.com/300x300/0891b2/ffffff?text=Water+Bottle',
                'status' => true,
                'tags' => ['water-bottle', 'insulated', 'stainless-steel'],
                'weight' => 0.6,
                'dimensions' => '3.2" x 3.2" x 10.8"',
                'meta_description' => '32oz vacuum insulated stainless steel water bottle',
            ],
        ];

        foreach ($products as $product) {
            \App\Models\Product::create($product);
        }

        // Create product variations for select products
        $this->createProductVariations();
    }

    private function createProductVariations(): void
    {
        // T-Shirt variations (sizes and colors)
        $tshirtSizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];
        $tshirtColors = [
            ['name' => 'Black', 'hex' => '#000000'],
            ['name' => 'White', 'hex' => '#ffffff'],
            ['name' => 'Navy', 'hex' => '#1e40af'],
            ['name' => 'Gray', 'hex' => '#6b7280'],
        ];

        // Classic Cotton T-Shirt variations
        $product = \App\Models\Product::where('sku', 'TSH-001')->first();
        if ($product) {
            foreach ($tshirtSizes as $index => $size) {
                \App\Models\ProductVariation::create([
                    'product_id' => $product->id,
                    'variation_type' => 'size',
                    'variation_value' => $size,
                    'price_adjustment' => $size === 'XXL' ? 3.00 : 0.00,
                    'stock_quantity' => 15,
                    'sku_suffix' => $size,
                    'is_default' => $size === 'M',
                ]);
            }

            foreach ($tshirtColors as $index => $color) {
                \App\Models\ProductVariation::create([
                    'product_id' => $product->id,
                    'variation_type' => 'color',
                    'variation_value' => $color['name'],
                    'price_adjustment' => 0.00,
                    'stock_quantity' => 20,
                    'sku_suffix' => strtoupper(substr($color['name'], 0, 3)),
                    'is_default' => $color['name'] === 'Black',
                ]);
            }
        }

        // Hoodie variations (sizes)
        $hoodieSizes = ['S', 'M', 'L', 'XL', 'XXL'];
        $product = \App\Models\Product::where('sku', 'HOD-001')->first();
        if ($product) {
            foreach ($hoodieSizes as $size) {
                \App\Models\ProductVariation::create([
                    'product_id' => $product->id,
                    'variation_type' => 'size',
                    'variation_value' => $size,
                    'price_adjustment' => $size === 'XXL' ? 5.00 : 0.00,
                    'stock_quantity' => 8,
                    'sku_suffix' => $size,
                    'is_default' => $size === 'L',
                ]);
            }
        }

        // Water Bottle variations (colors)
        $bottleColors = [
            ['name' => 'Matte Black', 'adjustment' => 0.00],
            ['name' => 'Ocean Blue', 'adjustment' => 2.00],
            ['name' => 'Forest Green', 'adjustment' => 2.00],
            ['name' => 'Sunset Orange', 'adjustment' => 2.00],
        ];

        $product = \App\Models\Product::where('sku', 'BTL-001')->first();
        if ($product) {
            foreach ($bottleColors as $color) {
                \App\Models\ProductVariation::create([
                    'product_id' => $product->id,
                    'variation_type' => 'color',
                    'variation_value' => $color['name'],
                    'price_adjustment' => $color['adjustment'],
                    'stock_quantity' => 12,
                    'sku_suffix' => strtoupper(str_replace(' ', '', substr($color['name'], 0, 3))),
                    'is_default' => $color['name'] === 'Matte Black',
                ]);
            }
        }
    }
}
