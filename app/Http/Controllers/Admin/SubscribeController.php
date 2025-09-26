<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscribe;
use Illuminate\Http\Request;
use DataTables;


class SubscribeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $data = Subscribe::orderBy('id', 'desc');

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '<a href="#" data-url="' . route('subscribers.destroy', encrypt($row['id'])) . '" class="item-delete text-body item-delete"><i class="bx bxs-trash-alt"></i></a>';
                    return $btn;
                })

                ->rawColumns(['action'])
                ->make(true);
        }

        return view('admin.subscribe.index');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $subscribe_id = decrypt($id);
        $record =  Subscribe::where('id', $subscribe_id)->first();
        if ($record) {
            $record->delete();
            return response()->json(['status' => 'success', 'table' => 'subscribersTable']);
        } else {
            return response()->json(['status' => 'error']);
        }
    }
}
