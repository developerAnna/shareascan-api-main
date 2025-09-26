<?php

use App\Models\Setting;
use App\Models\OrderItems;
use App\Services\MerchMake;
use chillerlan\QRCode\QRCode;
use App\Models\ProductSetting;
use chillerlan\QRCode\QROptions;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Common\EccLevel;
use Illuminate\Support\Facades\Storage;
use chillerlan\QRCode\Output\QRGdImagePNG;
use Illuminate\Pagination\LengthAwarePaginator;
use chillerlan\QRCode\Output\QRCodeOutputException;


/**
 * Write code on Method
 *
 * @return response()
 */
if (!function_exists('get_options')) {
    function get_options($name)
    {
        $setting = Setting::where('name', $name)->first();
        if ($setting) {
            return $setting->value;
        } else {
            return null;
        }
    }
}

if (!function_exists('get_category_title')) {
    function get_category_title($title)
    {
        $category_title_data = explode('>', $title);
        $category_title = isset($category_title_data[1]) ? $category_title_data[1] : $category_title_data[0];
        return $category_title;
    }
}


if (!function_exists('xss_clean')) {
    function xss_clean($data)
    {
        // Fix &entity\n;
        $data = str_replace(array('&amp;', '&lt;', '&gt;'), array('&amp;amp;', '&amp;lt;', '&amp;gt;'), $data);
        $data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
        $data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
        $data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');

        // Remove any attribute starting with "on" or xmlns
        $data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $data);

        // Remove javascript: and vbscript: protocols
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $data);
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $data);
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $data);

        // Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $data);

        // Remove namespaced elements (we do not need them)
        $data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);

        do {
            // Remove really unwanted tags
            $old_data = $data;
            $data = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $data);
        } while ($old_data !== $data);

        // we are done...
        return $data;
    }
}


if (!function_exists('process_string')) {

    function process_string($search_replace, $string)
    {
        $result = $string;
        foreach ($search_replace as $key => $value) {
            $result = str_replace($key, $value, $result);
        }
        return $result;
    }
}

if (!function_exists('getNewArrivalProducts')) {

    function getNewArrivalProducts()
    {

        $merchMake = new MerchMake();
        $merchmake_products = $merchMake->getProducts();

        $db_products = ProductSetting::where('type', 'new_arrivals')->pluck('product_id')->toArray();
        $new_arrival_products = [];

        if (!empty($merchmake_products)) {
            foreach ($merchmake_products['data'] as $product) {
                if (in_array($product['id'], $db_products)) {
                    $new_arrival_products[] = $product;
                }
            }
            return $new_arrival_products;
        }

        return $new_arrival_products;
    }
}

if (!function_exists('getBestSellerProducts')) {

    function getBestSellerProducts()
    {

        $merchMake = new MerchMake();
        $merchmake_products = $merchMake->getProducts();
        $db_products = ProductSetting::where('type', 'best_seller')->pluck('product_id')->toArray();

        $best_seller_products = [];
        if (!empty($merchmake_products)) {
            foreach ($merchmake_products['data'] as $product) {
                if (in_array($product['id'], $db_products)) {
                    $best_seller_products[] = $product;
                }
            }
            return $best_seller_products;
        }

        return $best_seller_products;
    }
}

if (!function_exists('getShopPageCategories')) {

    function getShopPageCategories()
    {
        $db_categories = ProductSetting::where('type', 'category')->get();
        return $db_categories;
    }
}

// if (!function_exists('getShopPageCategories')) {

//     function getShopPageCategories()
//     {

//         // $merchMake = new MerchMake();
//         // $merchmake_categories = $merchMake->getCategories();

//         // $db_categories = ProductSetting::where('type', 'category')->pluck('product_id')->toArray();
//         $db_categories = ProductSetting::where('type', 'category')->get();

//         return $db_categories;

//         // $shop_page_categories = [];
//         // if (!empty($merchmake_categories)) {
//         //     foreach ($merchmake_categories as $category) {
//         //         if (in_array($category['id'], $db_categories)) {
//         //             $shop_page_categories[] = $category;
//         //         }

//         //         if (isset($category['sub_categories']) && !empty($category['sub_categories'])) {
//         //             foreach ($category['sub_categories'] as $sub_category) {
//         //                 if (in_array($sub_category['id'], $db_categories)) {
//         //                     $shop_page_categories[] = $sub_category;
//         //                 }
//         //             }
//         //         }
//         //     }
//         //     return $shop_page_categories;
//         // }

//         // return $shop_page_categories;
//     }
// }

if (!function_exists('getProductPrice')) {

    function getProductPrice($product_id)
    {
        // Initialize variables to store the highest and lowest prices
        $highestPrice = null;
        $lowestPrice = null;

        $merchMake = new MerchMake();
        $merchmake_product = $merchMake->getSingleProduct($product_id);
        if (!empty($merchmake_product)) {
            $product_variations = $merchmake_product['variations'];

            // Check if the product variations are not empty
            if (!empty($product_variations)) {
                foreach ($product_variations as $product_variation) {
                    // Check if price is available for the current variation
                    if (isset($product_variation['price'])) {
                        $price = $product_variation['price'];

                        // Set the first price as both highest and lowest
                        if ($highestPrice === null || $price > $highestPrice) {
                            $highestPrice = $price;
                        }

                        if ($lowestPrice === null || $price < $lowestPrice) {
                            $lowestPrice = $price;
                        }
                    }
                }
            }

            // Return the array with highest and lowest prices
            return [
                'max_price' => $highestPrice,
                'min_price' => $lowestPrice
            ];
        } else {
            return [
                'max_price' => null,
                'min_price' => null
            ];
        }
    }
}


if (! function_exists('paginateArray')) {
    /**
     * Paginate an array or collection of items.
     *
     * @param  array|Collection  $items
     * @param  int               $perPage
     * @param  int|null          $page
     * @param  array             $options
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    function paginateArray($items, int $perPage = 16, int $page = null, array $options = [])
    {
        // Ensure $items is a collection
        $collection = $items instanceof Collection ? $items : collect($items);

        // Determine the current page or default to 1
        $page = $page ?: (request()->get('page', 1));

        // Slice the collection to get items for the current page
        $sliced = $collection->slice(($page - 1) * $perPage, $perPage)->values();

        // Create a new LengthAwarePaginator instance
        return new LengthAwarePaginator(
            $sliced,
            $collection->count(),    // Total items
            $perPage,               // Items per page
            $page,                  // Current page
            array_merge([
                'path'  => request()->url(),
                'query' => request()->query(),  // Preserve query parameters
            ], $options)
        );
    }
}

if (! function_exists('getVariations')) {
    function getVariations($products)
    {
        $variations = [];
        $size = [];
        $color = [];
        // dd($products);
        if (!empty($products)) {
            foreach ($products as $product) {
                if (isset($product['variations']) && !empty($product['variations'])) {
                    foreach ($product['variations'] as $product_variation) {
                        if (isset($product_variation['size_name']) && !empty($product_variation['size_name'])) {
                            if (!in_array($product_variation['size_name'], $size)) {
                                $size[] = $product_variation['size_name'];
                            }
                        }
                        if (isset($product_variation['color_name']) && !empty($product_variation['color_name'])) {
                            if (!in_array($product_variation['color_name'], $color)) {
                                $color[] = $product_variation['color_name'];
                            }
                        }
                    }
                }
            }
        }
        $variations =  [
            'size_array' => $size,
            'color_array' => $color,
        ];
        return $variations;
    }
}

if (! function_exists('getProductsFromCategoryId')) {
    function getProductsFromCategoryId($category_id)
    {
        $merchMake = new MerchMake();
        $merchmake_products = $merchMake->getProducts();
        $category_products = [];

        if (!empty($merchmake_products)) {
            foreach ($merchmake_products['data'] as $merchmake_product) {
                if (isset($merchmake_product['categories']) && !empty($merchmake_product['categories'])) {
                    foreach ($merchmake_product['categories'] as $merchmake_category) {
                        if ($merchmake_category['id'] == $category_id) {
                            $category_products[] = $merchmake_product;
                        }
                    }
                }
            }
        }

        return $category_products;
    }
}

if (! function_exists('GetSelectedCategoryTitle')) {
    function GetSelectedCategoryTitle($category)
    {
        $title = "New Arrivals";
        if ($category == "new_arrival") {
            $title = "New Arrivals";
        } elseif ($category == "bestseller") {
            $title = "Best Sellers";
        } else {
            $product_setting = ProductSetting::where('product_id', $category)->where('type', 'category')->first();
            if ($product_setting) {
                $title = $product_setting->title;
            } else {
                $title = "";
            }
        }
        return $title;
    }
}


// if (! function_exists('sortProducts')) {
//     function sortProducts($products, $sorting_type)
//     {
//         if (!empty($products)) {
//             if ($sorting_type === 'a_to_z') {
//                 $sortedProducts = collect($products)->sortBy(function ($product) {
//                     // Extract first character and first number if present in the title
//                     preg_match('/\d+/', $product['title'], $matches);

//                     $firstChar = strtolower($product['title'][0]);
//                     $number = isset($matches[0]) ? (int) $matches[0] : 0; // Default to 0 if no number found

//                     return [$firstChar, $number];
//                 })->values()->all();
//             } elseif ($sorting_type === 'z_to_a') {
//                 // Sort products by reverse order, alphabetically and numerically
//                 $sortedProducts = collect($products)->sortByDesc(function ($product) {
//                     preg_match('/\d+/', $product['title'], $matches); // Get the first number

//                     // If number exists, return an array with first letter and number; otherwise, just the letter
//                     $firstChar = strtolower($product['title'][0]);
//                     $number = isset($matches[0]) ? (int) $matches[0] : 0;

//                     return [$firstChar, $number];
//                 })->values()->all();
//             } else {
//                 return $products;
//             }

//             return $sortedProducts;
//         }

//         return [];
//     }
// }



if (! function_exists('sortProducts')) {

    function sortProducts(array $products, string $sorting_type): array
    {
        if (empty($products)) {
            return [];
        }

        // Convert the array to a collection for sorting
        $collection = collect($products);

        if ($sorting_type === 'a_to_z') {
            // Sort ascending (A to Z) using a custom callback that uses strnatcasecmp
            $sortedProducts = $collection->sort(function ($a, $b) {
                return strnatcasecmp($a['title'], $b['title']);
            })->values()->all();
        } elseif ($sorting_type === 'z_to_a') {
            // Sort descending (Z to A) by reversing the order
            $sortedProducts = $collection->sort(function ($a, $b) {
                // Compare in reverse
                return strnatcasecmp($b['title'], $a['title']);
            })->values()->all();
        } else {
            // If no valid sorting type is provided, return original array
            return $products;
        }

        return $sortedProducts;
    }
}


if (! function_exists('getProductWithSelectedColor')) {
    function getProductWithSelectedColor($products, $color)
    {
        // Initialize an array to store the selected products
        $selected_color_products = [];

        // Initialize an array to keep track of product IDs that have already been added
        $added_product_ids = [];

        if (!empty($products)) {
            foreach ($products as $product) {
                if (isset($product['variations']) && !empty($product['variations'])) {
                    foreach ($product['variations'] as $product_variation) {
                        if (isset($product_variation['color_name']) && in_array($product_variation['color_name'], $color)) {
                            // Check if the product has already been added using its unique identifier
                            if (isset($product['id']) && !in_array($product['id'], $added_product_ids)) {
                                // Log the product (optional)
                                Log::info($product);

                                // Add the product to the selected array
                                $selected_color_products[] = $product;

                                // Add the product ID to the 'added' array to avoid duplicates
                                $added_product_ids[] = $product['id'];
                            }
                        }
                    }
                }
            }
        }

        return $selected_color_products;
    }
}

if (! function_exists('getProductWithSelectedSize')) {
    function getProductWithSelectedSize($products, $size)
    {
        // Initialize an array to store the selected products
        $selected_size_products = [];

        // Initialize an array to keep track of product IDs that have already been added
        $added_product_ids = [];

        if (!empty($products)) {
            foreach ($products as $product) {
                if (isset($product['variations']) && !empty($product['variations'])) {
                    foreach ($product['variations'] as $product_variation) {

                        if (isset($product_variation['size_name']) && in_array($product_variation['size_name'], $size)) {
                            // Check if the product has already been added using its unique identifier
                            if (isset($product['id']) && !in_array($product['id'], $added_product_ids)) {
                                // Log the product (optional)
                                // Log::info($product);

                                // Add the product to the selected array
                                $selected_size_products[] = $product;

                                // Add the product ID to the 'added' array to avoid duplicates
                                $added_product_ids[] = $product['id'];
                            }
                        }
                    }
                }
            }
        }

        return $selected_size_products;
    }
}


if (! function_exists('searchProduct')) {
    function searchProduct($search_text)
    {
        $merchMake = new MerchMake();
        $merchmake_products = $merchMake->getProducts();

        $search_products = [];
        if (!empty($merchmake_products)) {
            foreach ($merchmake_products['data'] as $product) {
                // Check if the search text is found in the product's title (case insensitive)
                if (stripos($product['title'], $search_text) !== false) {
                    // If it matches, add to the search products array
                    $search_products[] = $product;
                }
            }
            return $search_products;
        }

        return $search_products;
    }
}

if (! function_exists('getWishListProducts')) {
    function getWishListProducts($product_ids)
    {
        $merchMake = new MerchMake();
        $merchmake_products = $merchMake->getProducts();

        $wishlist_products = [];
        if (!empty($merchmake_products) && isset($merchmake_products['data'])) {
            foreach ($merchmake_products['data'] as $product) {
                Log::info($product_ids);
                Log::info($product['id']);
                // Ensure both product ID and the wishlist product IDs exist and match
                if (in_array($product['id'], $product_ids)) {
                    $wishlist_products[] = $product;
                }
            }
        }

        return $wishlist_products;
    }
}


if (! function_exists('getSingleProductVariations')) {
    function getSingleProductVariations($product)
    {
        $variations = [];

        // Check if product exists and has variations
        if (!empty($product)) {
            if (isset($product['variations']) && !empty($product['variations'])) {
                foreach ($product['variations'] as $product_variation) {
                    if (!empty($product_variation)) {
                        foreach ($product_variation as $key => $value) {
                            // Exclude 'id', 'price', and 'images'
                            if ($key != 'id' && $key != 'price' && $key != 'images') {
                                // If the key already exists, append the value, else create a new array
                                if (!isset($variations[$key])) {
                                    $variations[$key] = [];
                                }
                                if (!in_array($value, $variations[$key])) {
                                    $variations[$key][] = $value;
                                }
                            }
                        }
                    }
                }
            }
        }
        return $variations;
    }
}


if (! function_exists('getSelectedVariationPrice')) {
    function getSelectedVariationPrice($product_id, $size, $color)
    {
        $merchMake = new MerchMake();
        $merchmake_product = $merchMake->getSingleProduct($product_id);

        if($merchmake_product == false){
            return null;
        }

        $variation = [];

        foreach ($merchmake_product['variations'] as $product_variation) {
            if (!empty($product_variation)) {
                if ($product_variation['size_name'] == $size && $product_variation['color_name'] == $color) {
                    $variation = $product_variation;
                    break;
                }
            }
        }

        return $variation;
    }
}

if (! function_exists('checkProductExits')) {
    function checkProductExits($product_id)
    {
        $merchMake = new MerchMake();
        $merchmake_products = $merchMake->getProducts();

        $checkProduct = false;
        $product_title = null;

        foreach ($merchmake_products['data'] as $product) {
            if ($product['id'] == $product_id) {
                $checkProduct = true;
                $product_title = $product['title'];
                break;
            }
        }

        return ['productExists' => $checkProduct, 'product_title' => $product_title];
    }
}


if (! function_exists('getProductImage')) {
    function getProductImage($product_id, $variation_id)
    {
        $merchMake = new MerchMake();
        $merchmake_product = $merchMake->getSingleProduct($product_id);
        $images = [];

        if (isset($merchmake_product) && !empty($merchmake_product)) {
            $images['image_url'] = $merchmake_product['img_url'];

            if (isset($merchmake_product['variations']) && !empty($merchmake_product['variations'])) {
                foreach ($merchmake_product['variations'] as $variation) {
                    if ($variation['id'] == $variation_id) {
                        $images['variation_images'] = $variation['images'];
                        break;
                    }
                }
            }
        }

        return $images;
    }
}

if (! function_exists('getRGBValue')) {
    function getRGBValue($color, $opacity = false)
    {
        // Default value as [0, 0, 0]
        $default = [0, 0, 0];

        // Return null if color is empty
        if (empty($color)) {
            return null;
        }

        // Remove the '#' if present
        if ($color[0] == '#') {
            $color = substr($color, 1);
        }

        // Process 6-character and 3-character hex codes
        if (strlen($color) == 6) {
            $hex = [$color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5]];
        } elseif (strlen($color) == 3) {
            $hex = [$color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2]];
        } else {
            return $default;
        }

        // Convert hex to decimal RGB values
        $rgb = array_map('hexdec', $hex);

        // If opacity is specified, include it in the result
        if ($opacity) {
            if (abs($opacity) > 1) {
                $opacity = 1.0;
            }
            // Output as RGBA format (with opacity), but keeping array structure
            $output = [...$rgb, $opacity];
        } else {
            // Return only the RGB values
            $output = $rgb;
        }

        // Store the result in the database in array format without quotes
        return $output;
    }
}

// if (!function_exists('generateQR')) {
//     function generateQR($hexa_color, $RGB_color, $qr_data, $source)
//     {
//         // Custom QR Image Class with Logo
//         class QRImageWithLogo extends QRGdImagePNG
//         {
//             public function dump(string|null $file = null, string|null $logo = null): string
//             {

//                 $logo ??= '';
//                 $this->options->returnResource = true;

//                 if ($logo && (!is_file($logo) || !is_readable($logo))) {
//                     throw new QRCodeOutputException('invalid logo');
//                 }

//                 parent::dump($file); // Generate the QR code first


//                 if ($logo) {
//                     // Add logo after generating QR code
//                     $im = imagecreatefrompng($logo);
//                     $w = imagesx($im);
//                     $h = imagesy($im);
//                     $lw = (($this->options->logoSpaceWidth - 2) * $this->options->scale);
//                     $lh = (($this->options->logoSpaceHeight - 2) * $this->options->scale);
//                     $ql = ($this->matrix->getSize() * $this->options->scale);
//                     imagecopyresampled($this->image, $im, (($ql - $lw) / 2), (($ql - $lh) / 2), 0, 0, $lw, $lh, $w, $h);
//                 }

//                 $imageData = $this->dumpImage();
//                 Storage::disk('public')->put($file, $imageData);

//                 return $imageData;
//             }
//         }

//         $options = new QROptions;

//         $drawCircularModules = false;

//         // QR code configuration
//         $options->version = 6;
//         $options->scale = 62;
//         $options->imageTransparent = true; // Enable transparent image output
//         $options->drawCircularModules = (bool) $drawCircularModules;
//         $options->circleRadius = $drawCircularModules ? 0.45 : 0;
//         $options->eccLevel = EccLevel::H;
//         $options->addLogoSpace = true;
//         $options->logoSpaceWidth = 13;
//         $options->logoSpaceHeight = 13;

//         // Set the color scheme for the QR code with dynamic color input
//         $options->moduleValues = [
//             QRMatrix::M_FINDER_DARK => $RGB_color,
//             QRMatrix::M_FINDER_DOT => $RGB_color,
//             QRMatrix::M_ALIGNMENT_DARK => $RGB_color,
//             QRMatrix::M_TIMING_DARK => $RGB_color,
//             QRMatrix::M_FORMAT_DARK => $RGB_color,
//             QRMatrix::M_VERSION_DARK => $RGB_color,
//             QRMatrix::M_DATA_DARK => $RGB_color,
//             QRMatrix::M_DARKMODULE => $RGB_color,
//         ];

//         // Initialize QRCode generator
//         $qrcode = new QRCode($options);
//         $qrcode->addByteSegment($qr_data);

//         // Generate a timestamped filename for uniqueness
//         $timestamp = now()->timestamp;
//         $filename = 'qr_code_' . $timestamp . '.png';

//         if ($source == 'admin') {
//             $folderPath = 'qrcodes/';
//         } else {
//             $folderPath = 'OrderItemQrcodes/';
//         }

//         $filePath = $folderPath . $filename; // Relative path for the disk

//         $qrOutputInterface = new QRImageWithLogo($options, $qrcode->getQRMatrix());
//         $logoPath = public_path('admin/assets/img/logo/mainlogo.png');
//         $qrOutputInterface->dump($folderPath . $filename, $logoPath);

//         // Return the filename and path for further use
//         return [
//             'filename' => $filename,
//             'filepath' => $folderPath . $filename,
//         ];
//     }
// }

if (!function_exists('generateQR')) {
    function generateQR($hexa_color, $RGB_color, $qr_data, $source)
    {
        if (!class_exists('QRImageWithLogo')) {
            // Custom QR Image Class with Logo
            class QRImageWithLogo extends QRGdImagePNG
            {
                public function dump(string|null $file = null, string|null $logo = null): string
                {
                    $logo ??= '';
                    $this->options->returnResource = true;

                    if ($logo && (!is_file($logo) || !is_readable($logo))) {
                        throw new QRCodeOutputException('invalid logo');
                    }

                    parent::dump($file); // Generate the QR code first

                    if ($logo) {
                        // Add logo after generating QR code
                        $im = imagecreatefrompng($logo);
                        $w = imagesx($im);
                        $h = imagesy($im);
                        $lw = (($this->options->logoSpaceWidth - 2) * $this->options->scale);
                        $lh = (($this->options->logoSpaceHeight - 2) * $this->options->scale);
                        $ql = ($this->matrix->getSize() * $this->options->scale);
                        imagecopyresampled($this->image, $im, (($ql - $lw) / 2), (($ql - $lh) / 2), 0, 0, $lw, $lh, $w, $h);
                    }

                    $imageData = $this->dumpImage();
                    Storage::disk('public')->put($file, $imageData);

                    return $imageData;
                }
            }
        }

        $options = new QROptions;

        $drawCircularModules = false;

        // QR code configuration
        $options->version = 6;
        $options->scale = 62;
        $options->imageTransparent = true; // Enable transparent image output
        $options->drawCircularModules = (bool) $drawCircularModules;
        $options->circleRadius = $drawCircularModules ? 0.45 : 0;
        $options->eccLevel = EccLevel::H;
        $options->addLogoSpace = true;
        $options->logoSpaceWidth = 13;
        $options->logoSpaceHeight = 13;

        // Set the color scheme for the QR code with dynamic color input
        $options->moduleValues = [
            QRMatrix::M_FINDER_DARK => $RGB_color,
            QRMatrix::M_FINDER_DOT => $RGB_color,
            QRMatrix::M_ALIGNMENT_DARK => $RGB_color,
            QRMatrix::M_TIMING_DARK => $RGB_color,
            QRMatrix::M_FORMAT_DARK => $RGB_color,
            QRMatrix::M_VERSION_DARK => $RGB_color,
            QRMatrix::M_DATA_DARK => $RGB_color,
            QRMatrix::M_DARKMODULE => $RGB_color,
        ];

        // Initialize QRCode generator
        $qrcode = new QRCode($options);
        $qrcode->addByteSegment($qr_data);

        // Generate a timestamped filename for uniqueness
        $timestamp = now()->timestamp;
        $filename = 'qr_code_' . $timestamp . '.png';

        // Set folder path based on the source
        if ($source == 'admin') {
            $folderPath = 'qrcodes/';
        } else if($source == 'post') {
            $folderPath = 'posts/';
        }else {
            $folderPath = 'OrderItemQrcodes/';
        }

        $filePath = $folderPath . $filename; // Relative path for the disk

        // Generate the QR code with the logo
        $qrOutputInterface = new QRImageWithLogo($options, $qrcode->getQRMatrix());
        $logoPath = public_path('admin/assets/img/logo/mainlogo.png');
        $qrOutputInterface->dump($filePath, $logoPath);

        // Return the filename and path for further use
        return [
            'filename' => $filename,
            'filepath' => $folderPath . $filename,
        ];
    }
}




if (!function_exists('generateUniqueCode')) {
    function generateUniqueCode($length = 10)
    {
        do {
            $code = substr(str_shuffle("0123456789"), 0, $length);
            $exists = OrderItems::where('uuid', $code)->exists();
        } while ($exists);

        return $code;
    }
}

if (! function_exists('getProductTitle')) {
    function getProductTitle($product_id)
    {
        $merchMake = new MerchMake();
        $merchmake_product = $merchMake->getSingleProduct($product_id);
        $title = null;

        if (isset($merchmake_product) && !empty($merchmake_product)) {
            $title = $merchmake_product['title'];
        }

        return $title;
    }
}
