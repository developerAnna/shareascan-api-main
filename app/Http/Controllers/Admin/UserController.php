<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use DataTables;


class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $data = User::orderBy('id', 'desc');

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '<a href="' . route('users.show', $row['id']) . '" class="btn rounded-pill btn-icon btn-outline-primary"><i class="bx bxs-show"></i></a>';
                    return $btn;
                })

                ->editColumn('name', function ($row) {
                    return $row->name . ' ' . $row->last_name;
                })

                ->editColumn('email_verified_at', function ($row) {
                    if (is_null($row->email_verified_at)) {
                        return '<span class="badge bg-label-danger" text-capitalized="">No</span>';
                    } else {
                        return '<span class="badge bg-label-success" text-capitalized="">Yes</span>';
                    }
                })

                ->rawColumns(['action', 'email_verified_at'])
                ->make(true);
        }

        return view('admin.User.index');
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::find($id);
        return view('admin.User.show', compact('user'));
    }


}
