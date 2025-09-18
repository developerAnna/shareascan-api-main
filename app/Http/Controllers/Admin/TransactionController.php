<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use DataTables;


class TransactionController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $data = Transaction::whereHas('order', function ($query) {
                $query->whereNull('deleted_at');
            })->orderBy('id', 'desc');

            return Datatables::of($data)
                ->addIndexColumn()

                ->editColumn('merchmake_order_id', function ($row) {
                    return $row->order->merchmake_order_id ?? null;
                })

                ->editColumn('order_payment_status', function ($row) {
                    return $row->order->payment_status ?? null;
                })

                ->rawColumns(['merchmake_order_id', 'order_payment_status'])
                ->make(true);
        }

        return view('admin.transaction.index');
    }
}
