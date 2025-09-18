<?php

namespace App\Http\Controllers\Admin;

use DataTables;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Models\OrderItemQrCodes;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;


class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $data = Order::where('status', 1)->withTrashed()->orderBy('id', 'desc');

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '';
                    if ($row->trashed()) {
                        // Show restore button if soft deleted
                        $btn .= '<a href="#" data-url="' . route('orders.restore', encrypt($row['id'])) . '" class="item-restore text-body"><i class="bi bi-arrow-counterclockwise me-1"></i></a>';
                    } else {
                        $btn .= '<a href="' . route('orders.show', $row['id']) . '" class="item-show text-body"><i class="bx bxs-show"></i></a>';

                        // Show delete button if not soft deleted
                        $btn .= '<a href="#" data-type="order" data-url="' . route('orders.destroy', encrypt($row['id'])) . '" class="item-delete text-body"><i class="bx bxs-trash-alt"></i></a>';
                    }
                    return $btn;
                })

                ->editColumn('user_id', function ($row) {
                    return $row->user->name;
                })

                ->editColumn('order_id', function ($row) {
                    return $row->id;
                })

                ->rawColumns(['action', 'order_id'])
                ->make(true);
        }

        return view('admin.Order.index');
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
        $order = Order::where('id', $id)->where('status', 1)->first();
        return view('admin.Order.show', compact('order'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $order_id = decrypt($id);
        $record =  Order::where('id', $order_id)->where('status', 1)->first();
        if ($record) {
            // if (!empty($record->orderItems)) {
            //     foreach ($record->orderItems as $orderItem) {
            //         if (!empty($orderItem->getOrderItemQrCodes)) {
            //             foreach ($orderItem->getOrderItemQrCodes as $code) {

            //                 $qr = OrderItemQrCodes::where('id', $code->id)->first();
            //                 $path = storage_path('app/public/' . $code->qr_image_path);

            //                 if (file_exists($path)) {
            //                     File::delete($path);
            //                 }
            //                 $qr->delete();
            //             }
            //         }
            //         $orderItem->delete();
            //     }
            // }
            $record->delete();
            return response()->json(['status' => 'success', 'table' => 'orderTable']);
        } else {
            return response()->json(['status' => 'error']);
        }
    }

    public function restoreOrder(Request $request, $id)
    {
        $order_id = decrypt($id);
        $record =  Order::withTrashed()->where('id', $order_id)->first();
        if ($record) {
            $record->restore();
            return response()->json(['status' => 'success', 'table' => 'orderTable']);
        } else {
            return response()->json(['status' => 'error']);
        }
    }
}
