<?php

namespace App\Http\Controllers\Admin;

use DataTables;
use App\Models\FAQ;
use Illuminate\Http\Request;
use App\Http\Requests\FaqRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;


class FaqController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $data = FAQ::orderBy('id', 'desc');

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '<a href="' . route('faq.edit', $row['id']) . '" class="item-edit text-body"><i class="bx bxs-edit"></i></a>';
                    $btn .= '<a href="#" data-url="' . route('faq.destroy', encrypt($row['id'])) . '" class="item-delete text-body item-delete"><i class="bx bxs-trash-alt"></i></a>';

                    return $btn;
                })
                ->editColumn('status', function ($row) {
                    if ($row->status == 1) {
                        return '<span class="badge bg-label-success" text-capitalized="">Active</span>';
                    } else {
                        return '<span class="badge bg-label-secondary" text-capitalized="">Inactive</span>';
                    }
                })
                ->rawColumns(['action', 'status'])
                ->make(true);
        }

        return view('admin.faq.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.faq.form');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(FaqRequest $request)
    {
        DB::beginTransaction();

        try {

            FAQ::create($request->all());
            // Commit the transaction
            DB::commit();

            if (!$request->ajax()) {
                return redirect()->route('faq.index')->with('success', 'Saved Successfully');
            }
        } catch (\Exception $e) {
            // Rollback the transaction if anything goes wrong
            DB::rollback();

            Log::error('faq creation failed: ' . $e->getMessage());

            return redirect()->route('faq.create')
                ->with('error', 'An error occurred while saving the category. Please try again.')
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $faq = FAQ::find($id);
        return view('admin.faq.form', compact('faq'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(FaqRequest $request, string $id)
    {
        DB::beginTransaction();

        try {
            $updateData = $request->except('_token', '_method');

            FAQ::where('id', $id)->update($updateData);
            // Commit the transaction
            DB::commit();

            if (!$request->ajax()) {
                return redirect()->route('faq.index')->with('success', 'Saved Successfully');
            }
        } catch (\Exception $e) {
            // Rollback the transaction if anything goes wrong
            DB::rollback();

            Log::error('faq creation failed: ' . $e->getMessage());

            return redirect()->route('faq.update', $id)
                ->with('error', 'An error occurred while saving the category. Please try again.')
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $faq_id = decrypt($id);
        $record =  FAQ::where('id', $faq_id)->first();
        if ($record) {
            $record->delete();
            return response()->json(['status' => 'success', 'table' => 'faqTable']);
        } else {
            return response()->json(['status' => 'error']);
        }
    }
}
