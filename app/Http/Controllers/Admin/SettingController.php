<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Setting;
use App\Services\MerchMake;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\ProductSetting;

class SettingController extends Controller
{

    public function appdeletion(Request $request)
    {
        dd($request->all());
    }

    public function create()
    {
        return view('admin.setting.form');
    }

    public function store(Request $request)
    {
        $tabName = 'store_settings';
        if (isset($request->email_settings) && $request->email_settings == "email_settings") {
            $request->validate([
                'mail_type' => 'required',
                'from_email' => 'required|email',
                'from_name' => 'required',
                'smtp_host' => 'required',
                'smtp_port' => 'required',
                'smtp_username' => 'required',
                'smtp_password' => 'required',
                'smtp_encryption' => 'required',
            ]);
            $tabName = 'email_settings';
        } elseif (isset($request->store_settings) && $request->store_settings == "store_settings") {
            $request->validate([
                'merchmake_store_id' => 'required|string',
                'merchmake_access_token' => 'required|string',
            ]);
            $tabName = 'store_settings';
        } elseif (isset($request->getemail_settings) && $request->getemail_settings == "getemail_settings") {
            $request->validate([
                'get_contact_us_email_on' => 'required|email',
            ]);
            $tabName = 'getemail_settings';
        } elseif (isset($request->payment_settings) && $request->payment_settings == "payment_settings") {
            $tabName = 'payment_settings';
        } else {
        }


        // Start the transaction
        // DB::beginTransaction();
        // DB::transaction();

        try {
            foreach ($_POST as $key => $value) {
                if ($key == "_token" || $key == "email_settings" || $key == "store_settings") {
                    continue;
                }

                $data = array();
                $data['value'] = is_array($value) ? json_encode($value) : $value;

                if (Setting::where('name', $key)->exists()) {
                    Setting::where('name', '=', $key)->update($data);
                } else {
                    $data['name'] = $key;
                    $data['created_at'] = Carbon::now();
                    Setting::insert($data);
                }
            }

            // DB::commit();

            // Return a response after successful update
            return redirect()->back()->with('success', 'Settings updated successfully.')->with('activeTab', $tabName);
        } catch (\Exception $e) {
            // dd($e->getMessage());
            // // If any exception occurs, rollback the transaction
            // DB::rollBack();

            // Log the exception (optional, for debugging)
            Log::error('Error updating settings: ' . $e->getMessage());
            // Return an error message
            return redirect()->back()->with('error', 'Failed to update settings. Please try again.');
        }
    }

    public function shopPageSettings()
    {
        $merchMake = new MerchMake();
        // Call the getProducts function
        $merchmake_products = $merchMake->getProducts();
        $merchmake_categories = $merchMake->getCategories();

        if ($merchmake_categories === false) {
            return redirect()->back()
                ->with('error', 'An error occurred while getting category data from merchmake. Please try again.')
                ->withInput();
        }

        if (!empty($merchmake_products)) {
            $merchmake_products =  $merchmake_products['data'];
        }

        $best_seller_products = ProductSetting::where('type', 'best_seller')->pluck('product_id')->toArray();
        $new_arrival_products = ProductSetting::where('type', 'new_arrivals')->pluck('product_id')->toArray();
        $db_categories = ProductSetting::where('type', 'category')->pluck('product_id')->toArray();
        $hot_products = ProductSetting::where('type', 'hot_product')->pluck('product_id')->toArray();
        return view('admin.setting.shopPage.form', compact('merchmake_products', 'best_seller_products', 'merchmake_categories', 'new_arrival_products', 'db_categories', 'hot_products'));
    }

    public function shopPageSettingsStore(Request $request)
    {
        $tabName = 'shop_page_products_settings';
        if (isset($request->shop_page_products_settings) && $request->shop_page_products_settings == "shop_page_products_settings") {
            $tabName = 'shop_page_products_settings';
        } elseif (isset($request->hot_products) && !empty($request->hot_products)) {
            $tabName = 'hot_items_setting';
        }

        // Start the transaction
        DB::beginTransaction();

        try {

            // store best sellers products
            if ($request->best_sellers_products) {
                $submitted_product_ids = [];

                // Process the submitted products
                foreach ($request['best_sellers_products'] as $key => $value) {

                    $product_details = explode('start_title', $value);

                    $product_id = isset($product_details[0]) ? $product_details[0] : null;
                    $product_title = isset($product_details[1]) ? $product_details[1] : null;

                    if (!empty($product_id) && !empty($product_title)) {
                        // Add the product_id to the submitted list
                        $submitted_product_ids[] = $product_id;

                        // Check if the product is already marked as "best_seller"
                        $check_exits = ProductSetting::where('product_id', $product_id)
                            ->where('type', 'best_seller')
                            ->first();

                        // If not, create a new record
                        if (empty($check_exits)) {
                            ProductSetting::create([
                                'product_id' => $product_id,
                                'title' => $product_title,
                                'type' => 'best_seller',
                            ]);
                        }
                    }
                }

                // Find products that are currently marked as "best_seller" but were not in the submitted list
                ProductSetting::where('type', 'best_seller')
                    ->whereNotIn('product_id', $submitted_product_ids)
                    ->delete();
            } else {
                ProductSetting::where('type', 'best_seller')
                    ->delete();
            }

            // store new arrival products
            if ($request->new_arrival_products) {
                $submitted_product_ids = [];

                // Process the submitted products
                foreach ($request['new_arrival_products'] as $key => $value) {

                    $product_details = explode('start_title', $value);

                    $product_id = isset($product_details[0]) ? $product_details[0] : null;
                    $product_title = isset($product_details[1]) ? $product_details[1] : null;

                    if (!empty($product_id) && !empty($product_title)) {
                        // Add the product_id to the submitted list
                        $submitted_product_ids[] = $product_id;

                        // Check if the product is already marked as "new_arrivals"
                        $check_exits = ProductSetting::where('product_id', $product_id)
                            ->where('type', 'new_arrivals')
                            ->first();

                        // If not, create a new record
                        if (empty($check_exits)) {
                            ProductSetting::create([
                                'product_id' => $product_id,
                                'title' => $product_title,
                                'type' => 'new_arrivals',
                            ]);
                        }
                    }
                }

                // Find products that are currently marked as "new_arrivals" but were not in the submitted list
                ProductSetting::where('type', 'new_arrivals')
                    ->whereNotIn('product_id', $submitted_product_ids)
                    ->delete();
            } else {
                ProductSetting::where('type', 'new_arrivals')
                    ->delete();
            }

            // store categories to display on the shop page
            if ($request->categories) {

                $submitted_category_ids = [];

                // Process the submitted products
                foreach ($request['categories'] as $key => $value) {

                    $category_details = explode('start_title', $value);

                    $category_id = isset($category_details[0]) ? $category_details[0] : null;
                    $category_title = isset($category_details[1]) ? $category_details[1] : null;

                    if (!empty($category_id) && !empty($category_title)) {
                        // Add the product_id to the submitted list
                        $submitted_category_ids[] = $category_id;

                        // Check if the product is already marked as "new_arrivals"
                        $check_exits = ProductSetting::where('product_id', $category_id)
                            ->where('type', 'category')
                            ->first();

                        // If not, create a new record
                        if (empty($check_exits)) {
                            ProductSetting::create([
                                'product_id' => $category_id,
                                'title' => $category_title,
                                'type' => 'category',
                            ]);
                        }
                    }
                }

                // Find products that are currently marked as "new_arrivals" but were not in the submitted list
                ProductSetting::where('type', 'category')
                    ->whereNotIn('product_id', $submitted_category_ids)
                    ->delete();
            } else {
                ProductSetting::where('type', 'category')
                    ->delete();
            }

            // store best sellers products
            if ($request->hot_products) {
                $submitted_product_ids = [];

                // Process the submitted products
                foreach ($request['hot_products'] as $key => $value) {

                    $product_details = explode('start_title', $value);

                    $product_id = isset($product_details[0]) ? $product_details[0] : null;
                    $product_title = isset($product_details[1]) ? $product_details[1] : null;

                    if (!empty($product_id) && !empty($product_title)) {
                        // Add the product_id to the submitted list
                        $submitted_product_ids[] = $product_id;

                        // Check if the product is already marked as "best_seller"
                        $check_exits = ProductSetting::where('product_id', $product_id)
                            ->where('type', 'hot_product')
                            ->first();

                        // If not, create a new record
                        if (empty($check_exits)) {
                            ProductSetting::create([
                                'product_id' => $product_id,
                                'title' => $product_title,
                                'type' => 'hot_product',
                            ]);
                        }
                    }
                }

                // Find products that are currently marked as "best_seller" but were not in the submitted list
                ProductSetting::where('type', 'hot_product')
                    ->whereNotIn('product_id', $submitted_product_ids)
                    ->delete();
            } else {
                ProductSetting::where('type', 'hot_product')
                    ->delete();
            }

            DB::commit();

            // Return a response after successful update
            return redirect()->back()->with('success', 'Settings updated successfully.')->with('activeTab', $tabName);
        } catch (\Exception $e) {
            // If any exception occurs, rollback the transaction
            DB::rollBack();

            // Log the exception (optional, for debugging)
            Log::error('Error updating Products settings: ' . $e->getMessage());
            // Return an error message
            return redirect()->back()->with('error', 'Failed to update Products settings. Please try again.');
        }
    }

    public function authenticationSettings(Request $request)
    {
        return view('admin.setting.authentication.form');
    }

    public function storeAuthenticationSettings(Request $request)
    {
        $tabName = 'google';
        if (isset($request->google) && $request->google == "google") {
            $tabName = 'google';
        } elseif (isset($request->facebook) && $request->facebook == "facebook") {
            $tabName = 'facebook';
        } elseif (isset($request->apple) && $request->apple == "apple") {
            $tabName = 'apple';
        } else {
        }


        // Start the transaction
        DB::beginTransaction();

        try {
            foreach ($_POST as $key => $value) {

                // Skip processing for the specified keys
                if ($key == "_token" || $key == "google" || $key == "facebook" || $key == "apple") {
                    continue; // Skip the rest of the loop for these keys
                }


                $data = array();
                $data['value'] = is_array($value) ? json_encode($value) : $value;

                // Check if the setting already exists
                if (Setting::where('name', $key)->exists()) {
                    // Update the existing setting
                    Setting::where('name', '=', $key)->update($data);
                } else {
                    // Insert new setting
                    $data['name'] = $key;
                    $data['created_at'] = Carbon::now();
                    Setting::insert($data);
                }
            }

            DB::commit();
            // Return a response after successful update
            return redirect()->back()->with('success', 'Settings updated successfully.')->with('activeTab', $tabName);
        } catch (\Exception $e) {
            // If any exception occurs, rollback the transaction
            DB::rollBack();

            // Log the exception (optional, for debugging)
            Log::error('Error updating settings: ' . $e->getMessage());
            // Return an error message
            return redirect()->back()->with('error', 'Failed to update settings. Please try again.');
        }
    }
}
