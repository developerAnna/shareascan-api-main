<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Exception\RequestException;

class MerchMake
{

    public function getSettings()
    {
        $store_id = get_options('merchmake_store_id');
        $access_token = get_options('merchmake_access_token');
        $base_url = get_options('merchmake_base_url');

        return [
            'store_id' => trim($store_id),
            'access_token' => trim($access_token),
            'base_url' => trim($base_url),
        ];
    }


    public function getCategories()
    {
        $settings = $this->getSettings();

        // Initialize the Guzzle client
        $client = new Client(['verify' => false]);

        try {
            // Ensure base_url has no trailing spaces
            $baseUrl = trim($settings['base_url']);

            // Send the GET request using Guzzle
            $response = $client->request('GET', rtrim($baseUrl, '/') . '/categories', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $settings['access_token'], // Add authorization token
                ],
                'query' => [
                    'store_id' => $settings['store_id'], // Use store_id from settings
                ]
            ]);

            $result = json_decode($response->getBody()->getContents(), true);
            return $result;
        } catch (RequestException $e) {
            // dd($e->getMessage());
            Log::info($e->getMessage());
            // Handle the exception if the request fails
            return false;
            // return redirect()->back()->with('error', 'Request failed: ' . $e->getMessage());
        }
    }

    public function getProducts()
    {

        // Get the settings
        $settings = $this->getSettings();

        // Initialize the Guzzle client
        $client = new Client(['verify' => false]);

        try {
            // Ensure base_url has no leading/trailing spaces
            $baseUrl = trim($settings['base_url']);

            // Send the GET request using Guzzle
            $response = $client->request('GET', rtrim($baseUrl, '/') . '/products', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $settings['access_token'], // Add authorization token
                ],
                'query' => [
                    'store_id' => $settings['store_id'], // Use store_id from settings
                ]
            ]);

            $result = json_decode($response->getBody()->getContents(), true);

            return $result;
        } catch (RequestException $e) {
            Log::info($e->getMessage());
            // Handle the exception if the request fails
            return redirect()->back()->with('error', 'Request failed: ' . $e->getMessage());
        }
    }

    public function getSingleProduct($product_id)
    {
        // Get the settings
        $settings = $this->getSettings();

        // Initialize the Guzzle client
        $client = new Client(['verify' => false]);

        try {
            // Ensure base_url has no leading/trailing spaces
            $baseUrl = trim($settings['base_url']);

            // Send the GET request using Guzzle
            $response = $client->request('GET', rtrim($baseUrl, '/') . '/products/' . $product_id, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $settings['access_token'], // Add authorization token
                ],
                'query' => [
                    'store_id' => $settings['store_id'], // Use store_id from settings
                ]
            ]);

            $result = json_decode($response->getBody()->getContents(), true);

            return $result;
        } catch (RequestException $e) {

            Log::info($e->getMessage());
            return false;
        }
    }

    public function createOrder($order)
    {
        $settings = $this->getSettings();

        // Initialize the Guzzle client
        $client = new Client(['verify' => false]);

        // Prepare shipping address
        $shipping = $order->shippingAddress;
        $shippingAddress = [
            'first_name' => $shipping->first_name,
            'last_name' => $shipping->last_name,
            'address_1' => $shipping->address_1,
            'address_2' => $shipping->address_2,
            'city' => $shipping->city,
            'state' => $shipping->state,
            'phone' => $shipping->phone,
            'country_code' => ($shipping->country_code == "US") ? $shipping->country_code : 'US',
            'zip' => $shipping->zip,
        ];

        // Prepare line items
        $lineItems = [];
        if ($order->orderItems) {
            foreach ($order->orderItems as $item) {
                $custom_decorations = [];
                if (!empty($item->getOrderItemQrCodes)) {
                    foreach ($item->getOrderItemQrCodes as $qr_code) {
                        $custom_decorations[strtolower($qr_code->position)] = asset('storage/' . $qr_code->qr_image_path);
                    }
                }

                // Prepare each line item
                $lineItems[] = [
                    'product_variation_id' => $item->product_variation_id,
                    'quantity' => $item->qty,
                    'external_id' => $item->id,
                    'custom_decorations' => $custom_decorations,
                ];
            }
        }

        // Prepare the payload to send in the API request
        $payload = [
            "store_id" => $settings['store_id'],
            "external_id" => $order->id,  // A unique external ID for the order (you can adjust as needed)
            "email" => $shipping->email,
            "shipping_address" => $shippingAddress,
            "items" => $lineItems,
        ];

        $baseUrl = trim($settings['base_url']);

        try {
            $response = $client->request('POST', rtrim($baseUrl, '/') . '/orders', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $settings['access_token'],
                ],
                'json' => $payload,
            ]);

            // Parse the response
            $result = json_decode($response->getBody()->getContents(), true);

            $order->update([
                'merchmake_response' => json_encode($result),
            ]);

            if ($result && !empty($result['invoice']) && !empty($result['status'])) {
                $order->update([
                    'merchmake_order_id' => $result['id'],
                    'merchmake_invoice_id' => $result['invoice'],
                    'merchmake_order_status' => $result['status'],
                ]);
            }
            return $result;
        } catch (RequestException $e) {
            $order->update([
                'merchmake_response' => json_encode($e->getMessage()),
            ]);
            Log::error('Request failed: ' . $e->getMessage());
            Log::error('Full error response: ' . $e->getResponse()->getBody()->getContents());
            // return redirect()->back()->with('error', 'Request failed: ' . $e->getMessage());
            return false;
        }
    }

    public function getOrder($order_id)
    {
        // Get the settings
        $settings = $this->getSettings();

        // Initialize the Guzzle client
        $client = new Client(['verify' => false]);

        try {
            // Ensure base_url has no leading/trailing spaces
            $baseUrl = trim($settings['base_url']);

            // Send the GET request using Guzzle
            $response = $client->request('GET', rtrim($baseUrl, '/') . '/orders/' . $order_id, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $settings['access_token'], // Add authorization token
                ],
                'query' => [
                    'store_id' => $settings['store_id'], // Use store_id from settings
                ]
            ]);

            $result = json_decode($response->getBody()->getContents(), true);
            return $result;
        } catch (RequestException $e) {

            Log::info($e->getMessage());

            return false;

            // Handle the exception if the request fails
            // return redirect()->back()->with('error', 'Request failed: ' . $e->getMessage());
        }
    }
}
