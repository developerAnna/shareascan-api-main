<?php

namespace App\Http\Controllers\Admin;

use DataTables;
use App\Models\Review;
use App\Models\ReviewImages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReviewRequest;
use Illuminate\Support\Facades\Storage;


class ReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $data = Review::orderBy('id', 'desc');

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '<a href="' . route('review.edit', $row['id']) . '" class="item-edit text-body"><i class="bx bxs-edit"></i></a>';
                    $btn .= '<a href="#" data-url="' . route('review.destroy', encrypt($row['id'])) . '" class="item-delete text-body item-delete"><i class="bx bxs-trash-alt"></i></a>';

                    return $btn;
                })

                ->editColumn('user_id', function ($row) {
                    return $row->user->name . ' ' . $row->user->last_name;
                })

                ->editColumn('status', function ($row) {
                    if ($row->status == 1) {
                        return '<span class="badge bg-label-success" text-capitalized="">Active</span>';
                    } else {
                        return '<span class="badge bg-label-danger" text-capitalized="">Inactive</span>';
                    }
                })

                ->rawColumns(['action', 'status'])
                ->make(true);
        }

        return view('admin.review.index');
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
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $review = Review::find($id);
        return view('admin.review.form', compact('review'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ReviewRequest $request, string $id)
    {
        DB::beginTransaction();
        try {

            Review::where('id', $id)->update(['star_count' => $request->star_count, 'status' => $request->status, 'content' => $request->content]);
            // Commit the transaction
            DB::commit();

            if (!$request->ajax()) {
                return redirect()->route('review.index')->with('success', 'Saved Successfully');
            }
        } catch (\Exception $e) {
            // Rollback the transaction if anything goes wrong
            DB::rollback();

            Log::error('review updation failed: ' . $e->getMessage());

            return redirect()->route('review.update', $id)
                ->with('error', 'An error occurred while review updation. Please try again.')
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $review_id = decrypt($id);
        $record =  Review::where('id', $review_id)->first();
        if ($record) {
            if (isset($record->reviewImages) && !empty($record->reviewImages)) {
                foreach ($record->reviewImages as $reviewImage) {
                    if (Storage::disk('public')->exists($reviewImage->file_path)) {
                        Storage::delete($reviewImage->file_path);
                    }
                    $reviewImage->delete();
                }
            }
            $record->delete();
            return response()->json(['status' => 'success', 'table' => 'reviewTable']);
        } else {
            return response()->json(['status' => 'error']);
        }
    }

    public function reviewImageDelete($id)
    {
        $review_img_id = decrypt($id);
        $reviewImage = ReviewImages::find($review_img_id);
        if ($reviewImage) {
            if (Storage::disk('public')->exists($reviewImage->file_path)) {
                Storage::delete($reviewImage->file_path);
            }
            $reviewImage->delete();
            return response()->json(['status' => 'success']);
        }
    }
}
