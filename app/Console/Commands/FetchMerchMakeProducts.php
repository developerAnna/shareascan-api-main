<?php

namespace App\Console\Commands;

use App\Services\MerchMake;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FetchMerchMakeProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-merch-make-products';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch products from MerchMake API and store as JSON';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {

            $merchMake = new MerchMake();

            // Fetch products from API
            $merchmake_products = $merchMake->getProducts();

            // Validate response
            if (empty($merchmake_products)) {
                Log::warning('MerchMake API returned empty response');
                return;
            }

            // Log::info('MerchMake products fetched successfully at ' . now()->toDateTimeString());

            // Ensure directory exists
            $directory = storage_path('app/public/merchmake');

            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            // Store DIRECT API response (no wrapper)
            file_put_contents(
                $directory . '/products.json',
                json_encode(
                    $merchmake_products,
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
                )
            );

            // -----------------------------
            // 2. BUILD PRODUCT PRICE JSON
            // -----------------------------
            $priceData = [];

            if (!empty($merchmake_products['data'])) {

                foreach ($merchmake_products['data'] as $product) {

                    if (empty($product['id']) || empty($product['variations'])) {
                        continue;
                    }

                    $min = null;
                    $max = null;

                    foreach ($product['variations'] as $variation) {

                        if (!isset($variation['price'])) {
                            continue;
                        }

                        $price = (float) $variation['price'];

                        $min = $min === null ? $price : min($min, $price);
                        $max = $max === null ? $price : max($max, $price);
                    }

                    if ($min !== null && $max !== null) {
                        $priceData[$product['id']] = [
                            'min_price' => $min,
                            'max_price' => $max
                        ];
                    }
                }
            }

            // -----------------------------
            // 3. STORE PRICE JSON
            // -----------------------------
            file_put_contents(
                $directory . '/product_prices.json',
                json_encode(
                    $priceData,
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
                )
            );

            $this->info('MerchMake products stored successfully in JSON file.'. now()->toDateTimeString());
        } catch (\Exception $e) {

            Log::error('MerchMake JSON store error', [
                'message' => $e->getMessage()
            ]);
        }
    }
}
