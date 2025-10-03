<?php

namespace App\Http\Controllers\Admin;

use DataTables;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\TestimonialRequest;


class TestimonialController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $data = Testimonial::orderBy('id', 'desc');

            return Datatables::of($data)
                ->addIndexColumn()

                ->addColumn('action', function ($row) {
                    $btn = '<a href="' . route('testimonials.edit', $row['id']) . '" class="btn rounded-pill btn-icon btn-outline-primary me-2"><i class="bx bxs-edit"></i></a>';
                    $btn .= '<a href="#" data-url="' . route('testimonials.destroy', encrypt($row['id'])) . '" class="btn rounded-pill btn-icon btn-outline-danger item-delete"><i class="bx bxs-trash-alt"></i></a>';

                    return $btn;
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

        return view('admin.testimonial.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.testimonial.form');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TestimonialRequest $request)
    {
        DB::beginTransaction();

        try {
            Testimonial::create($request->validated());

            DB::commit();

            if (!$request->ajax()) {
                return redirect()->route('testimonials.index')->with('success', 'Saved Successfully');
            }
        } catch (\Exception $e) {

            DB::rollback();

            Log::error('Testimonial creation failed: ' . $e->getMessage());

            return redirect()->route('testimonials.create')
                ->with('error', 'An error occurred while saving the testimonial. Please try again.')
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
        $testimonial = Testimonial::find($id);
        return view('admin.testimonial.form', compact('testimonial'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TestimonialRequest $request, string $id)
    {
        DB::beginTransaction();

        try {
            $updateData = $request->except('_token', '_method');

            Testimonial::where('id', $id)->update($updateData);
            DB::commit();

            if (!$request->ajax()) {
                return redirect()->route('testimonials.index')->with('success', 'Saved Successfully');
            }
        } catch (\Exception $e) {
            DB::rollback();

            Log::error('testimonials updatation failed: ' . $e->getMessage());

            return redirect()->route('testimonials.update', $id)
                ->with('error', 'An error occurred while updating the testimonials. Please try again.')
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $testimonial_id = decrypt($id);
        $record =  Testimonial::where('id', $testimonial_id)->first();
        if ($record) {
            $record->delete();
            return response()->json(['status' => 'success', 'table' => 'testimonialsTable']);
        } else {
            return response()->json(['status' => 'error']);
        }
    }
}
