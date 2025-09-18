<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $productTypes = [
            'T-Shirt', 'Hoodie', 'Sweatshirt', 'Tank Top', 'Long Sleeve',
            'Baseball Cap', 'Beanie', 'Snapback', 'Trucker Hat',
            'Tote Bag', 'Backpack', 'Messenger Bag', 'Fanny Pack',
            'Coffee Mug', 'Travel Mug', 'Water Bottle', 'Tumbler',
            'Sticker Pack', 'Phone Case', 'Keychain', 'Pin Set'
        ];

        $colors = ['Black', 'White', 'Navy', 'Gray', 'Red', 'Blue', 'Green', 'Purple', 'Orange'];
        $materials = ['Cotton', 'Polyester', 'Cotton Blend', 'Fleece', 'Canvas', 'Ceramic', 'Stainless Steel', 'Vinyl'];
        
        $productType = $this->faker->randomElement($productTypes);
        $color = $this->faker->randomElement($colors);
        $material = $this->faker->randomElement($materials);
        
        $name = $productType;
        if ($this->faker->boolean(60)) {
            $name = $color . ' ' . $name;
        }
        if ($this->faker->boolean(40)) {
            $name = $material . ' ' . $name;
        }

        $price = $this->faker->randomFloat(2, 9.99, 99.99);
        $comparePrice = $this->faker->boolean(30) ? $price + $this->faker->randomFloat(2, 5, 20) : null;

        return [
            'name' => $name,
            'description' => $this->faker->paragraph(3),
            'sku' => strtoupper($this->faker->unique()->bothify('???-###')),
            'price' => $price,
            'compare_at_price' => $comparePrice,
            'category_id' => $this->faker->numberBetween(1, 10),
            'stock_quantity' => $this->faker->numberBetween(0, 200),
            'image_url' => 'https://via.placeholder.com/600x600/' . substr(dechex($this->faker->hexColor), 1) . '/ffffff?text=' . urlencode($name),
            'thumbnail_url' => 'https://via.placeholder.com/300x300/' . substr(dechex($this->faker->hexColor), 1) . '/ffffff?text=' . urlencode($name),
            'status' => $this->faker->boolean(90),
            'featured' => $this->faker->boolean(20),
            'sort_order' => $this->faker->numberBetween(0, 100),
            'meta_description' => $this->faker->sentence(),
            'tags' => $this->faker->randomElements(['premium', 'comfortable', 'durable', 'stylish', 'trendy', 'classic', 'modern', 'vintage'], $this->faker->numberBetween(1, 4)),
            'weight' => $this->faker->randomFloat(2, 0.05, 2.0),
            'dimensions' => $this->faker->randomElement([
                'Various sizes',
                'One size fits most',
                '3" x 3" x 4"',
                '12" x 16" x 6"',
                '2" x 2"',
                '15" x 18" x 8"'
            ]),
        ];
    }

    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'featured' => true,
        ]);
    }

    public function onSale(): static
    {
        return $this->state(function (array $attributes) {
            $price = $attributes['price'] ?? $this->faker->randomFloat(2, 15, 50);
            return [
                'price' => $price,
                'compare_at_price' => $price + $this->faker->randomFloat(2, 5, 15),
            ];
        });
    }

    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock_quantity' => 0,
        ]);
    }
}
