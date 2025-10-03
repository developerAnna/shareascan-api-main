<?php

namespace App\Http\Controllers\Admin;

use DataTables;
use App\Models\Qrcodes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\QrcodeRequest;
use Illuminate\Support\Facades\File;

class QrcodeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $data = Qrcodes::orderBy('id', 'desc');

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '<a href="' . route('qrcode.show', $row['id']) . '" class="btn rounded-pill btn-icon btn-outline-primary me-2"><i class="bx bxs-show"></i></a>';
                    $btn .= '<a href="#" data-url="' . route('qrcode.destroy', encrypt($row['id'])) . '" class="btn rounded-pill btn-icon btn-outline-danger item-delete"><i class="bx bxs-trash-alt"></i></a>';

                    return $btn;
                })
                ->editColumn('qr_image_path', function ($row) {
                    $qrImagePath = asset('storage/' . $row['qr_image_path']);
                    $downloadLink = route('downloadQrImage', $row->id);

                    return '
                        <div>
                            <img src="' . $qrImagePath . '" alt="' . $row['qr_image'] . '" width="100" height="100" class="img-fluid rounded">
                            <br>
                            <a href="' . $downloadLink . '" class="btn btn-outline-primary btn-sm mt-2">
                                <i class="bx bx-download"></i> Download
                            </a>
                        </div>
                    ';
                })
                ->rawColumns(['action', 'qr_image_path'])
                ->make(true);
        }

        return view('admin.qrcode.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.qrcode.form');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(QrcodeRequest $request)
    {
        DB::beginTransaction();

        try {

            if ($request->hexa_color) {
                $getRGBValue = getRGBValue($request->hexa_color, $opacity = false);
                if (!empty($getRGBValue)) {
                    $generate_qr = generateQR($request->hexa_color, $getRGBValue, $request->qr_data, $source = 'admin');
                    if (!empty($generate_qr) && !empty($generate_qr['filename']) && !empty($generate_qr['filepath'])) {
                        $qrcode = new Qrcodes();
                        $qrcode->hexa_color = $request->hexa_color;
                        $qrcode->rgb_color = json_encode($getRGBValue);
                        $qrcode->qr_data = $request->qr_data;
                        $qrcode->qr_image = $generate_qr['filename'];
                        $qrcode->qr_image_path = $generate_qr['filepath'];
                        $qrcode->save();
                    }
                }
            }

            // Commit the transaction
            DB::commit();

            if (!$request->ajax()) {
                return redirect()->route('qrcode.index')->with('success', 'Saved Successfully');
            }
        } catch (\Exception $e) {
            DB::rollback();

            Log::error('qrcode creation failed: ' . $e->getMessage());

            return redirect()->route('qrcode.create')
                ->with('error', 'An error occurred while saving the qrcode. Please try again.')
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $qrcode = Qrcodes::find($id);
        return view('admin.qrcode.show', compact('qrcode'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id) {}

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id) {}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $faq_id = decrypt($id);
        $record =  Qrcodes::where('id', $faq_id)->first();
        if ($record) {
            if (!empty($record->qr_image)) {
                $path = storage_path('app/public/' . $record->qr_image_path);
                if (file_exists($path)) {
                    File::delete($path);
                }
            }
            $record->delete();
            return response()->json(['status' => 'success', 'table' => 'qrcodeTable']);
        } else {
            return response()->json(['status' => 'error']);
        }
    }

    public function downloadQrImage(Request $request, $id)
    {
        $qr = Qrcodes::where('id', $id)->first();

        if ($qr) {
            $file_path = storage_path('app/public/' . $qr->qr_image_path);  // Use public directory path

            if (file_exists($file_path)) {
                return response()->download($file_path);  // Initiate the download
            } else {
                return redirect()->back()->with('error', 'QR code image file not found.');
            }
        } else {
            return redirect()->back()->with('error', 'QR code not found.');
        }
    }
}
