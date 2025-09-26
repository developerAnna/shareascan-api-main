<?php

namespace App\Http\Controllers\API;

use App\Models\OrderItems;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\OrderItemQrCodes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Resources\QrcodesResurce;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\API\BaseController;

class QrCodeController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/get-qrcode-content/{order_item_id}",
     *     summary="Get QR Code Content for an Order Item",
     *     description="Fetches the QR code details associated with a specific order item.",
     *     tags={"QR Codes"},
     *     security={{"X-Access-Token": {}}},
     *     @OA\Parameter(
     *         name="order_item_id",
     *         in="path",
     *         required=true,
     *         description="The ID of the order item.",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="QR code details fetched successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="QR code details fetched successfully!"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="qrcode_id", type="integer", example=1),
     *                 @OA\Property(property="content", type="string", example="Sample QR Code Data"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-03-25T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-03-26T12:00:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order item not found.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Order item not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Something went wrong. Please try again.")
     *         )
     *     )
     * )
     */


    public function getQrCodeContent(Request $request, $order_item_id)
    {
        try {
            $order_item = OrderItems::where('id', $order_item_id)->first();

            if (empty($order_item)) {
                return $this->sendError('Error.', 'Order item not found.');
            }

            $qrcodes = OrderItemQrCodes::where('order_item_id', $order_item_id)->where('status', 1)->get();

            if (!empty($qrcodes) && $qrcodes->count() > 0) {
                return $this->sendResponse(QrcodesResurce::collection($qrcodes), 'QR code details fetched successfully!');
            } else {
                return $this->sendError('Error.', 'No QR code details found.');
            }
        } catch (\Exception $e) {
            return $this->sendError('Error fetching QR codes.', $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *     path="/api/update-qrcode-content",
     *     summary="Update QR Code Content",
     *     description="Updates the QR code content for a specific order item. It handles different content types such as website_url,google_maps,text,document or social",
     *     tags={"QR Codes"},
     *     security={{"X-Access-Token": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Request body for updating QR code content",
     *         @OA\JsonContent(
     *             required={"order_item_id", "qr_content", "qrcode_content_type"},
     *             @OA\Property(property="order_item_id", type="integer", description="The ID of the order item to update.", example=123),
     *             @OA\Property(property="qr_content", type="string", description="The new QR code content.", example="https://example.com"),
     *             @OA\Property(
     *                 property="qrcode_content_type",
     *                 type="string",
     *                 description="The content type of the QR code. Possible values are website_url,google_maps,text,document,social",
     *                 enum={"website_url", "google_maps", "text", "document", "social"},
     *                 example="website_url"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="QR code content updated successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="QR Code content updated successfully!"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="order_item_id", type="integer", example=123),
     *                 @OA\Property(property="qr_content", type="string", example="https://example.com"),
     *                 @OA\Property(property="qrcode_content_type", type="string", example="website_url")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation Error.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Validation Error."),
     *             @OA\Property(property="errors", type="object", additionalProperties={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No QR code found for the given order item.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="No QR code found for the given order item.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Failed to update the QR code content. Please try again.")
     *         )
     *     )
     * )
     */


    public function updateQrCodeContent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_item_id' => 'required|exists:order_items,id',
            'qr_content' => 'required',
            'qrcode_content_type' => 'required|in:website_url,google_maps,text,document,social'
        ], [
            'qrcode_content_type.required' => 'The qr code content type is required.',
            'qrcode_content_type.in' => 'The selected qr code content type is invalid. It should be website_url, google_maps, text, document or social.'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        try {
            $qrcodes = OrderItemQrCodes::where('order_item_id', $request->order_item_id)->get();

            if ($qrcodes->count() > 0) {
                foreach ($qrcodes as $qrcode) {
                    $this->removeDocumentFromStorage($qrcode->qr_content);
                    $qrcode->qr_content = $request->qr_content;
                    $qrcode->qrcode_content_type = $request->qrcode_content_type;
                    $qrcode->save();
                }

                // Document Handling
                if ($request->qrcode_content_type == "document") {
                    if ($request->qr_content) {
                        $document = $request->qr_content;

                        $this->removeDocumentFromStorage($qrcode->qr_content);

                        // Check if the document is base64-encoded
                        if (strpos($document, 'data:application/pdf;base64,') === 0) {
                            // Decode base64 PDF data and save as a PDF file
                            $documentData = base64_decode(preg_replace('/^data:application\/pdf;base64,/', '', $document));

                            if ($documentData === false) {
                                return response()->json(['status' => 'error', 'message' => 'Invalid Base64 PDF data']);
                            }

                            $fileName = time() . '-' . Str::random(10) . '.pdf'; // Save as a .pdf file
                            $filePath = 'QrcodeContentDocuments/' . $fileName;

                            Storage::disk('public')->put($filePath, $documentData);

                            // Update qr_content for all QR codes
                            foreach ($qrcodes as $qrcode) {
                                $qrcode->qr_content = $fileName;
                                $qrcode->save();
                            }
                        } else {
                            // Handle regular file upload (PDF, DOC, etc.)
                            if ($request->hasFile('qr_content')) {
                                $file = $request->file('qr_content');
                                $originalFileName = $file->getClientOriginalName();
                                $extension = $file->getClientOriginalExtension(); // Get the extension

                                $fileName = pathinfo($originalFileName, PATHINFO_FILENAME) . '_' . time() . '.' . $extension;
                                $path = $file->storeAs('QrcodeContentDocuments', $fileName, 'public');

                                // Update qr_content for all QR codes
                                foreach ($qrcodes as $qrcode) {
                                    $qrcode->qr_content = $fileName;
                                    $qrcode->save();
                                }
                            } else {
                                // Handle base64-encoded documents (PDF, DOC)
                                $docData = $request['qr_content'];
                                $decodedDocument = base64_decode($docData);

                                if ($decodedDocument === false) {
                                    return response()->json(['status' => 'error', 'message' => 'Invalid Base64 document data']);
                                }

                                // Detect the file type based on the Base64 string (optional)
                                $fileExtension = $this->detectFileExtension($docData) ?: 'pdf'; // Default to 'pdf' if unknown

                                $fileName = time() . '-' . Str::random(10) . '.' . $fileExtension;
                                $filePath = 'QrcodeContentDocuments/' . $fileName;

                                Storage::disk('public')->put($filePath, $decodedDocument);

                                // Update qr_content for all QR codes
                                foreach ($qrcodes as $qrcode) {
                                    $qrcode->qr_content = $fileName;
                                    $qrcode->save();
                                }
                            }
                        }
                    }
                }


                return $this->sendResponse(QrcodesResurce::collection($qrcodes), 'QR Codes content updated successfully!');
            }

            return $this->sendError('error.', 'No QR code found for the given order item.');
        } catch (\Exception $e) {
            Log::error('Error while updating the qr code content: ' . $e->getMessage());
            return $this->sendError('error.', 'Failed to update the qr code content. Please try again.');
        }
    }

    private function removeDocumentFromStorage($documentPath)
    {
        if ($documentPath && Storage::disk('public')->exists('QrcodeContentDocuments/' . $documentPath)) {
            Storage::disk('public')->delete('QrcodeContentDocuments/' . $documentPath);
        }
    }

    private function detectFileExtension($base64Data)
    {
        if (strpos($base64Data, 'data:application/pdf') === 0) {
            return 'pdf';
        } elseif (strpos($base64Data, 'data:application/msword') === 0) {
            return 'doc';
        } elseif (strpos($base64Data, 'data:application/vnd.openxmlformats-officedocument.wordprocessingml.document') === 0) {
            return 'docx';
        }

        // Add more conditions if needed for other file types
        return null;
    }


    /**
     * @OA\Post(
     *     path="api/active-deactivate-qr",
     *     summary="Deactivate or Activate QR Code",
     *     description="This API endpoint toggles the activation status of a QR code based on its current status (active or inactive).",
     *     tags={"QR Codes"},
     *     security={{"X-Access-Token": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Request body for deactivating or activating a QR code",
     *         @OA\JsonContent(
     *             required={"qrcode_id"},
     *             @OA\Property(property="qrcode_id", type="integer", description="The ID of the QR code to deactivate or activate.", example=123)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="QR code deactivated or activated successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="QR Code deactivated successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=123, description="The QR code ID"),
     *                 @OA\Property(property="status", type="integer", example=1, description="The status of the QR code: 1 for active, 0 for inactive."),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2023-10-10T15:00:00Z", description="Timestamp when the QR code was created."),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2023-10-11T12:00:00Z", description="Timestamp when the QR code was last updated.")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation Error.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Validation Error."),
     *             @OA\Property(property="errors", type="object", additionalProperties={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Failed to deactivate or activate the QR code.")
     *         )
     *     )
     * )
     */

    public function deactivateQR(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'qrcode_id' => 'required|exists:order_item_qr_codes,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        DB::beginTransaction();

        try {
            $msj = '';
            $qrcode = OrderItemQrCodes::where('id', $request->qrcode_id)->first();
            if ($qrcode->status == 1) {
                $qrcode->status = 0;
                $msj = "QR Code deactivated successfully.";
            } else if ($qrcode->status == 0) {
                $qrcode->status = 1;
                $msj = "QR Code activated successfully.";
            } else {
                $qrcode->status = 1;
                $msj = "QR Code deactivated successfully.";
            }

            $qrcode->save();

            DB::commit();
            return $this->sendResponse(new QrcodesResurce($qrcode), $msj);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in adding the product into recently view: ' . $e->getMessage());
            return $this->sendError('error.', 'Failed to add the product into recently view.');
        }
    }
}
