<?php

namespace App\Http\Controllers\Admin;

use DataTables;
use App\Models\Desing;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;


class DesignController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $data = Desing::orderBy('id', 'desc');

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '<a href="' . route('designs.edit', $row['id']) . '" class="btn rounded-pill btn-icon btn-outline-primary me-2"><i class="bx bxs-edit"></i></a>';
                    $btn .= '<a href="#" data-url="' . route('designs.destroy', encrypt($row['id'])) . '" class="btn rounded-pill btn-icon btn-outline-danger item-delete"><i class="bx bxs-trash-alt"></i></a>';

                    return $btn;
                })
                ->editColumn('image', function ($row) {
                    return '<img src="' . asset('storage/' . $row->image_path) . '" alt="' . ($row->image_name ?? 'Image') . '" width="50">';
                })
                ->rawColumns(['action', 'image'])
                ->make(true);
        }

        return view('admin.design.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.design.form');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // dd($request->all());
        DB::beginTransaction();

        try {

            $request->validate([
                'image_name' => 'required|image|mimes:jpg,jpeg,png,svg,webp',
            ]);

            $imageName = null;
            $imagePath = null;

            // Check if image file is present
            if ($request->hasFile('image_name')) {
                $image = $request->file('image_name');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $imagePath = $image->storeAs('DesignImages', $imageName, 'public');
            }

            Desing::create([
                'image_name'    => $imageName,
                'image_path'    => $imagePath,
                'x_axis'        => $request->x_axis ?? null,
                'y_axis'        => $request->y_axis ?? null,
            ]);

            DB::commit();

            return redirect()->route('designs.index')->with('success', 'Saved Successfully');
        } catch (\Exception $e) {

            DB::rollback();
            Log::error('design creation failed: ' . $e->getMessage());

            return redirect()->route('designs.create')
                ->with('error', 'An error occurred while saving the design.')
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
        $design = Desing::find($id);
        return view('admin.design.form', compact('design'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {

            $design = Desing::findOrFail($id);

            // Validation
            $request->validate([
                'image_name' => 'nullable|image|mimes:jpg,jpeg,png,svg,webp',
            ]);

            $imageName = $design->image_name; // keep old name by default
            $imagePath = $design->image_path; // keep old path by default

            // If new image uploaded
            if ($request->hasFile('image_name')) {

                // delete old file if exists
                if ($design->image_path && Storage::disk('public')->exists($design->image_path)) {
                    Storage::disk('public')->delete($design->image_path);
                }

                $image = $request->file('image_name');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $imagePath = $image->storeAs('DesignImages', $imageName, 'public');
            }

            // Update record
            $design->update([
                'image_name' => $imageName,
                'image_path' => $imagePath,
                'x_axis'     => $request->x_axis ?? null,
                'y_axis'     => $request->y_axis ?? null,
            ]);

            DB::commit();

            return redirect()->route('designs.index')->with('success', 'Updated Successfully');
        } catch (\Exception $e) {

            DB::rollback();
            Log::error('design update failed: ' . $e->getMessage());

            return redirect()->route('designs.edit', $id)
                ->with('error', 'An error occurred while updating the design.')
                ->withInput();
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $category_id = decrypt($id);
        $record =  Desing::where('id', $category_id)->first();
        if ($record) {
            if ($record->image_path && Storage::disk('public')->exists($record->image_path)) {
                Storage::disk('public')->delete($record->image_path);
            }
            $record->delete();
            return response()->json(['status' => 'success', 'table' => 'designTable']);
        } else {
            return response()->json(['status' => 'error']);
        }
    }
}
