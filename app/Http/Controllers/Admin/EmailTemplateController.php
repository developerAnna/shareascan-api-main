<?php

namespace App\Http\Controllers\Admin;

use Exception;
use DataTables;
use Illuminate\Http\Request;
use App\Models\EmailTemplate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\EmailTemplateRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;


class EmailTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $data = EmailTemplate::orderBy('id', 'desc');

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '<a href="' . route('email-templates.edit', $row['id']) . '" class="item-edit text-body"><i class="bx bxs-edit"></i></a>';
                    $btn .= '<a href="#" data-url="' . route('email-templates.destroy', encrypt($row['id'])) . '" class="item-delete text-body item-delete"><i class="bx bxs-trash-alt"></i></a>';

                    return $btn;
                })

                ->rawColumns(['action'])
                ->make(true);
        }

        return view('admin.setting.email-template.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.setting.email-template.form');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(EmailTemplateRequest $request)
    {

        DB::beginTransaction();

        try {
            $input = $request->all();
            $input['slug'] = $request->name;
            $template = EmailTemplate::create($input);
            DB::commit();

            if ($template) {
                return redirect()->route('email-templates.index')->with('success', 'Saved successfully');
            }
        } catch (Exception $e) {
            // Rollback the transaction if anything goes wrong
            DB::rollback();
            Log::error('email template creation failed: ' . $e->getMessage());

            return redirect()->route('email-templates.create')
                ->with('error', 'An error occurred while saving the email template. Please try again.')
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
        $vars = array(
            'contact-us'     =>  '{name} {email} {phone_number} {message}',
        );

        $email_template = EmailTemplate::where('id', $id)->first();
        return view('admin.setting.email-template.form', compact('email_template', 'vars'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(EmailTemplateRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $template = EmailTemplate::where('id', $id)->first();
            $input = $request->all();
            $input['slug'] = $request->name;
            $template->update($input);
            DB::commit();
            if ($template) {
                return redirect()->route('email-templates.index')->with('success', 'Saved successfully');
            }
        } catch (Exception $e) {
            DB::rollback();
            Log::error('email template update failed: ' . $e->getMessage());

            return redirect()->route('email-templates.edit', $id)
                ->with('error', 'An error occurred while saving the email template. Please try again.')
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $email_template_id = decrypt($id);
        $record =  EmailTemplate::where('id', $email_template_id)->first();
        if ($record) {
            $record->delete();
            return response()->json(['status' => 'success', 'table' => 'EmailTemplateTable']);
        } else {
            return response()->json(['status' => 'error']);
        }
    }

    public function ckEditorImageUpload(Request $request)
    {
        if ($request->hasFile('file')) {
            $originName = $request->file('file')->getClientOriginalName();
            $fileName = pathinfo($originName, PATHINFO_FILENAME);
            $extension = $request->file('file')->getClientOriginalExtension();
            $fileName = $fileName . '_' . time() . '.' . $extension;
            $request->file('file')->move(public_path('admin/CkeditorImages'), $fileName);
            $url = asset('admin/CkeditorImages/' . $fileName);
            return response()->json(['location' => $url]);
        }
    }

    public function ckEditorImageRemove(Request $request)
    {
        $imageKey = $request->input('imageKey');
        $folder = public_path('/admin/CkeditorImages/' . $imageKey);

        if (file_exists($folder)) {
            unlink($folder);
            return response()->json(['message' => 'Image deleted from public folder']);
        } else {
            return response()->json(['message' => 'Image does not exist in the public folder']);
        }
    }
}
