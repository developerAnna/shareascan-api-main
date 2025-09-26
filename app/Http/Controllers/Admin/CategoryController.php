<?php

namespace App\Http\Controllers\Admin;

use DataTables;
use App\Models\Category;
use App\Services\MerchMake;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;


class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $data = Category::orderBy('id', 'desc');

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '<a href="' . route('category.edit', $row['id']) . '" class="item-edit text-body"><i class="bx bxs-edit"></i></a>';
                    $btn .= '<a href="#" data-url="' . route('category.destroy', encrypt($row['id'])) . '" class="item-delete text-body item-delete"><i class="bx bxs-trash-alt"></i></a>';

                    return $btn;
                })
                ->editColumn('image', function ($row) {
                    return '<img src="' . asset('CategoryImages/' . $row['image']) . '" alt="' . $row['name'] . '" width="50" />';
                })
                ->rawColumns(['action', 'image'])
                ->make(true);
        }

        return view('admin.category.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $merchMake = new MerchMake();
        // Call the getCategories function
        $merchmake_categories = $merchMake->getCategories();
        if ($merchmake_categories === false) {
            return redirect()->back()
                ->with('error', 'An error occurred while getting category data from merchmake. Please try again.')
                ->withInput();
        }
        return view('admin.category.form', compact('merchmake_categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CategoryRequest $request)
    {
        DB::beginTransaction();

        try {
            // Check if the image file is present
            if ($request->file('image')) {
                $image = $request->file('image');
                $originalName = $image->getClientOriginalName();
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->move(public_path('CategoryImages'), $imageName);
            }

            $category = $request->input('category_id');

            // Split the category value into ID and Title
            list($category_id, $category_title) = explode('|', $category);

            Category::create(['category_id' => $category_id, 'title' => $category_title, 'description' => $request->description, 'image' => isset($imageName) ? $imageName : null]);

            // Commit the transaction
            DB::commit();

            if (!$request->ajax()) {
                return redirect()->route('category.index')->with('success', 'Saved Successfully');
            }
        } catch (\Exception $e) {
            // Rollback the transaction if anything goes wrong
            DB::rollback();

            Log::error('Category creation failed: ' . $e->getMessage());

            return redirect()->route('category.create')
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
        $merchMake = new MerchMake();
        // Call the getCategories function
        $merchmake_categories = $merchMake->getCategories();
        if ($merchmake_categories === false) {
            return redirect()->back()
                ->with('error', 'An error occurred while getting category data from merchmake. Please try again.')
                ->withInput();
        }
        $db_category = Category::find($id);
        return view('admin.category.form', compact('merchmake_categories', 'db_category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CategoryRequest $request, string $id)
    {
        // Begin transaction
        DB::beginTransaction();

        try {
            $categoryData = Category::find($id);
            // Check if the image file is present
            if ($request->file('image')) {

                if (!empty($categoryData->image)) {
                    // Define the image file path
                    $path = public_path('CategoryImages/' . $categoryData->image);
                    // Check if the file exists before attempting to delete
                    if (file_exists($path)) {
                        File::delete($path); // Delete the image
                    }
                }
                // Handle the image upload
                $image = $request->file('image');
                $originalName = $image->getClientOriginalName();
                $imageName = time() . '_' . $image->getClientOriginalName();

                // Move the image to the public path
                $image->move(public_path('CategoryImages'), $imageName);
            } else {
                // If no new image is uploaded, retain the old image
                $imageName = $categoryData->image; // Use the existing image name
            }

            $category = $request->input('category_id');

            // Split the category value into ID and Title
            list($category_id, $category_title) = explode('|', $category);

            $categoryData->update(['category_id' => $category_id, 'title' => $category_title, 'description' => $request->description, 'image' => isset($imageName) ? $imageName : null]);

            DB::commit();

            if (!$request->ajax()) {
                return redirect()->route('category.index')->with('success', 'Saved Successfully');
            }
        } catch (\Exception $e) {
            // Rollback the transaction if anything goes wrong
            DB::rollback();

            Log::error('Category creation failed: ' . $e->getMessage());

            return redirect()->route('category.edit', $id)
                ->with('error', 'An error occurred while saving the category. Please try again.')
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $category_id = decrypt($id);
        $record =  Category::where('id', $category_id)->first();
        if ($record) {
            if (!empty($record->image)) {
                // Define the image file path
                $path = public_path('CategoryImages/' . $record->image);
                // Check if the file exists before attempting to delete
                if (file_exists($path)) {
                    File::delete($path); // Delete the image
                }
            }
            $record->delete();
            return response()->json(['status' => 'success', 'table' => 'categoryTable']);
        } else {
            return response()->json(['status' => 'error']);
        }
    }
}
