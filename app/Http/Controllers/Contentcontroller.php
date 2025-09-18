<?php

namespace App\Http\Controllers;

use App\Models\OrderItems;
use function Livewire\store;
use Illuminate\Http\Request;

use App\Models\OrderItemQrCodes;
use Illuminate\Support\Facades\Storage;

class Contentcontroller extends Controller
{
    public function qrContent(Request $request, $user_id, $order_id, $order_item_id)
    {

        $qrcode = OrderItemQrCodes::where('order_item_id', $order_item_id)->first();

        if (!$qrcode) {
            // If no QR code is found for the order item, return an error or redirect
            return redirect()->back()->with('error', 'QR code not found!');
        }

        // Handle based on the content type of the QR code
        switch ($qrcode->qrcode_content_type) {
            case 'website_url':
                // Redirect to the website URL when the QR code is a website URL
                return redirect()->to($qrcode->qr_content);

            case 'google_maps':
                return redirect()->to('https://www.google.com/maps?q=' . urlencode($qrcode->qr_content));

            case 'document':

                $fileUrl = Storage::url('QrcodeContentDocuments/' . $qrcode->qr_content);

                // Pass the URL to the Blade view
                return view('Front.qr_content', compact('fileUrl'));

            case 'text':
                // If the QR code content is text, return a view with the text displayed
                return view('Front.qr_content', compact('qrcode'));

            case 'social':
                    // Redirect to the website URL when the QR code is a website URL
                    return redirect()->to($qrcode->qr_content);

            default:
                // If the content type is unknown, you can redirect or show an error
                return redirect()->back()->with('error', 'Invalid QR code content type!');
        }
    }

    public function ThankYou(Request $request){
        dd($request->all());
    }
}
