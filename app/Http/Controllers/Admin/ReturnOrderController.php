<?php

namespace App\Http\Controllers\Admin;

use Exception;
use DataTables;
use Stripe\Refund;
use Stripe\Stripe;
use PayPal\Api\Sale;
use App\Models\Order;
use PayPal\Api\Amount;
use PayPal\Api\Payment;
use App\Mail\GeneralMail;
use App\Models\ReturnOrder;
use App\Models\Transaction;
use PayPal\Rest\ApiContext;
use App\Utilities\Overrider;
use Illuminate\Http\Request;
use App\Mail\ReturnOrderMail;
use App\Models\EmailTemplate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use PayPal\Auth\OAuthTokenCredential;
use Illuminate\Support\Facades\Validator;

use PayPal\Api\Refund as paypalRefund;
use PayPal\Api\RefundRequest;

use PayPal\Exception\PayPalConnectionException;


class ReturnOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $data = ReturnOrder::whereHas('order', function ($query) {
                $query->whereNull('deleted_at');
            })->orderBy('id', 'desc');

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '<a href="' . route('return-orders.show', $row['id']) . '" class="item-show text-body"><i class="bx bxs-show"></i></a>';
                    $btn .= '<a href="' . route('return-orders.edit', $row['id']) . '" class="item-edit text-body"><i class="bx bxs-edit"></i></a>';
                    return $btn;
                })
                ->editColumn('is_send_to_merchmake', function ($row) {
                    if ($row->is_send_to_merchmake == 0) {
                        return '<span class="text-danger">No</span>';
                    } else if ($row->is_send_to_merchmake == 1) {
                        return '<span class="text-success">Yes</span>';
                    } else {
                        return null;
                    }
                })
                ->editColumn('refund', function ($row) {
                    if ($row->return_status == 'Processing') {
                        return '<span class="text-danger">Processing</span>';
                    } else if ($row->return_status != "Refunded") {
                        return '<a href="javascript:void(0);" class="refund-to-user btn btn-secondary btn-sm"
                                        style="display: inline-flex; align-items: center; padding: 5px 10px;"
                                        data-url="' . route('refundRequest', $row['id']) . '">
                                     Refund To User
                                </a>';
                    } else {
                        return '<span class="text-success">Refunded</span>';
                    }
                })
                ->editColumn('user_name', function ($row) {
                    return isset($row->order->user) ? $row->order->user->name . ' ' . $row->order->user->last_name : '';
                })
                ->editColumn('user_email', function ($row) {
                    return isset($row->order->user) ? $row->order->user->email : '';
                })
                ->editColumn('order_id', function ($row) {
                    return '<a href="' . route('orders.show', $row['order_id']) . '"  target="_blank" class="order-details-link" data-order-id="' . $row->order_id . '">' . $row->order_id . '</a>';
                })
                ->rawColumns(['action', 'is_send_to_merchmake', 'user_name', 'user_email', 'order_id', 'refund'])
                ->make(true);
        }

        return view('admin.returnOrder.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $return_order = ReturnOrder::find($id);
        return view('admin.returnOrder.show', compact('return_order'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $return_order = ReturnOrder::find($id);
        return view('admin.returnOrder.form', compact('return_order'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // dd($request->all());
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:225',  // Set max length to 225 characters
            'description' => 'required|string',     // Added 'string' rule for consistency
            'return_status' => 'required',
            'cancle_reason' => 'nullable|string|max:255|required_if:return_status,Canceled',
        ],[
            'cancle_reason' => 'The cancle reason field is required'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();

        try {

            $return_order = ReturnOrder::where('id', $id)->first();

            if (!$return_order) {
                throw new Exception('Return order not found.');
            }

            $return_order->update(['reason' => $request->reason, 'description' => $request->description, 'return_status' => $request->return_status, 'cancle_reason' => $request->cancle_reason ?? null]);

            if ($request->action == "submit_and_send") {

                Overrider::load("Settings");

                // Prepare the files for the email attachment
                $files = [];
                if (!empty($return_order->returnOrderImages)) {
                    foreach ($return_order->returnOrderImages as $img) {
                        $files[] = asset("storage/" . $img->image_path);
                    }
                }

                // Prepare the mail data
                $mailData = [
                    'title' => 'Return Order Request',
                    'return_order' => $return_order,
                    'files' => $files
                ];

                // Send the email
                $email = get_options('merchmake_support_email') ?? 'info@admin.shareascan.com';
                Mail::to($email)->send(new ReturnOrderMail($mailData));

                $return_order->update(['is_send_to_merchmake' => 1]);
            }

            if ($request->return_status == "Canceled") {

                Overrider::load("Settings");

                //Replace paremeter
                $replace = array(
                    '{order_id}'          => $return_order->order_id,
                    '{cancle_reason}'     => $return_order->cancle_reason,
                    '{name}'              => $return_order->order->user->name . ' ' . $return_order->order->user->last_name,
                );

                //Send contact email
                $template = EmailTemplate::where('slug', 'return-request-has-been-cancelled')->first();
                $template->body = process_string($replace, $template->body);

                Mail::to($return_order->order->user->email)->send(new GeneralMail($template));
            }


            // Commit the transaction
            DB::commit();

            if (!$request->ajax()) {
                return redirect()->route('return-orders.index')->with('success', 'Saved Successfully');
            }
        } catch (\Exception $e) {
            // Rollback the transaction if anything goes wrong
            DB::rollback();

            Log::error('return order updation failed: ' . $e->getMessage());

            return redirect()->route('return-orders.edit', $id)
                ->with('error', 'An error occurred while updating the return order. Please try again.')
                ->withInput();
        }
    }


    // public function sendRequestInMerchmake(string $id)
    // {
    //     try {
    //         // Fetch the return order by id
    //         $return_order = ReturnOrder::where('id', $id)->first();

    //         if (!$return_order) {
    //             throw new Exception('Return order not found.');
    //         }

    //         // Prepare the files for the email attachment
    //         $files = [];
    //         if (!empty($return_order->returnOrderImages)) {
    //             foreach ($return_order->returnOrderImages as $img) {
    //                 $files[] = asset("storage/" . $img->image_path);
    //             }
    //         }

    //         // Prepare the mail data
    //         $mailData = [
    //             'title' => 'Return Order Request',
    //             'return_order' => $return_order,
    //             'files' => $files
    //         ];

    //         // Send the email
    //         $email = 'admin@gmail.com';
    //         Mail::to($email)->send(new ReturnOrderMail($mailData));

    //         $return_order->update(['is_send_to_merchmake' => 1]);
    //         return response()->json(['status' => 'success', 'table' => 'returnORderTable']);
    //     } catch (\Exception $e) {
    //         Log::error('Error sending return order email: ' . $e->getMessage());
    //         return response()->json(['error' => 'There was an error sending the email. Please try again later.'], 500);
    //     }
    // }

    public function refundRequest(Request $request, $id)
    {

        return response()->json(['status' => 'success', 'table' => 'returnORderTable']);


        try {
            // Retrieve the return order
            $return_order = ReturnOrder::where('id', $id)->first();

            // Check if the order payment is completed
            if ($return_order->order->payment_status != "Completed") {
                return response()->json([
                    'status' => 'error',
                    'message' => "Order Payment hasn't completed",
                ]);
            }

            // stripe refund
            if ($return_order->order->payment_method == "Stripe") {

                $stripe_secret = get_options('stripe_client_id');

                Stripe::setApiKey($stripe_secret);
                $chargeId = $return_order->order->transaction->charge_id;
                $refund = Refund::create([
                    'charge' => $chargeId,
                ]);

                Log::info($refund);
            }

            // paypal refund
            if ($return_order->order->payment_method == "Paypal") {
                $order = Order::find($return_order->order_id);

                if (!$order || !$order->paypal_id) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Invalid order or missing PayPal transaction ID.',
                    ], 400);
                }

                try {
                    $client_id = get_options('paypal_client_id');
                    $client_secret = get_options('paypal_client_secret');

                    // Initialize PayPal API context
                    $apiContext = new ApiContext(
                        new OAuthTokenCredential($client_id, $client_secret)
                    );

                    $apiContext->setConfig([
                        'mode' => get_options('paypal_mode'),
                    ]);


                    //Get sale from payment
                    $paypalPayment = new Payment();
                    $paymentInfo = $paypalPayment->get($order->paypal_id, $apiContext);

                    $transactions = $paymentInfo->getTransactions();
                    if (empty($transactions[0])) {
                        return false;
                    }

                    $relatedResources = $transactions[0]->getRelatedResources();
                    if (empty($relatedResources[0])) {
                        return false;
                    }

                    $sale = $relatedResources[0]->getSale();

                    $refund = new paypalRefund();
                    $amt = (new Amount())->setTotal($order->orderItems->sum('total'))->setCurrency('USD');
                    $refund->setAmount($amt);
                    $refund->setReason('Sale refund');

                    $refundedSale = $sale->refund($refund, $apiContext);

                    if ($refundedSale->getState() === 'completed') {
                        if ($return_order) {
                            $return_order->update(['return_status' => 'Processing']);
                        }
                        return response()->json(['status' => 'success', 'table' => 'returnORderTable']);
                    }
                } catch (PayPalConnectionException $e) {
                    Log::error('PayPal Refund Error: ' . $e->getMessage());
                    return response()->json([
                        'status' => false,
                        'message' => 'PayPal refund failed.',
                        'error' => $e->getMessage(),
                    ], 500);
                } catch (\Throwable $th) {
                    return response()->json([
                        'status' => false,
                        'message' => 'An error occurred during refund.',
                        'error' => $th->getMessage(),
                    ], 500);
                }
            }

            return response()->json(['status' => 'success', 'table' => 'returnORderTable']);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Refund failed: ' . $e->getMessage(),
            ]);
        }
    }
}
