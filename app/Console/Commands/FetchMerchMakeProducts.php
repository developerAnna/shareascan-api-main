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

            Log::info('MerchMake products fetched successfully');

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

            $this->info('MerchMake products stored successfully in JSON file.');
        } catch (\Exception $e) {

            Log::error('MerchMake JSON store error', [
                'message' => $e->getMessage()
            ]);
        }
    }
}
