<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Order;
use GuzzleHttp\Client;
use App\Mail\GeneralMail;
use App\Models\OrderItems;
use App\Utilities\Overrider;
use GuzzleHttp\Psr7\Request;
use App\Models\EmailTemplate;
use App\Models\CartItemQrCodes;
use App\Mail\OrderPlacedAdminMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use GuzzleHttp\Exception\RequestException;

class OrderInMerchmakeService
{

    public function handlePaymentSucceed($order)
    {

        $merchMake = new MerchMake();
        $merchmake_order = $merchMake->createOrder($order);

        if ($merchmake_order === false) {
            return [
                'success' => false,
                'message' => 'Something went wrong while creating the order in merchmake.'
            ];
        }

        $cart_ids = OrderItems::where('order_id', $order->id)->pluck('cart_id')->toArray();

        $remove_cart_items = Cart::whereIn('id', $cart_ids)->get();

        foreach ($remove_cart_items as $cart) {
            CartItemQrCodes::where('cart_id', $cart->id)->delete();
            $cart->delete();
        }

        Overrider::load("Settings");

        //Replace paremeter
        $replace = array(
            '{order_id}'          => $order->id,
            '{name}'              => $order->shippingAddress->first_name . ' ' . $order->shippingAddress->last_name,
            '{order_status}'      => $order->order_status,
            '{payment_method}'      => $order->payment_method,
            '{payment_status}'      => $order->payment_status,
        );

        //Send contact email
        $template = EmailTemplate::where('slug', 'order-placed')->first();
        $template->body = process_string($replace, $template->body);

        Mail::to($order->shippingAddress->email)->send(new GeneralMail($template));

        // Prepare the mail data
        $mailData = [
            'title' => 'Order Placed',
            'order' => $order
        ];

        // Send the email
        $email = get_options('get_contact_us_email_on') ?? 'info@admin.shareascan.com';
        Log::info($email);
        Mail::to($email)->send(new OrderPlacedAdminMail($mailData));

        return ['success' => true];
    }

    public function handlePaymentRefund($order)
    {
        Overrider::load("Settings");

        //Replace paremeter
        $replace = array(
            '{order_id}'          => $order->id,
            '{name}'              => $order->shippingAddress->first_name . ' ' . $order->shippingAddress->last_name,
            '{payment_method}'      => $order->payment_method,
            '{payment_status}'      => $order->payment_status,
        );

        //Send contact email
        $template = EmailTemplate::where('slug', 'order-refund')->first();
        $template->body = process_string($replace, $template->body);

        Mail::to($order->shippingAddress->email)->send(new GeneralMail($template));
    }
}
