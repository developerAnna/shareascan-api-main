<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\ImageKitService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class MediaUploadController extends Controller
{
    /**
     * Upload media files
     */
    public function upload(Request $request): JsonResponse
    {
        // Add this at the top of the upload method
\Log::info('ImageKit credentials check', [
    'public_key' => config('services.imagekit.public_key'),
    'private_key' => config('services.imagekit.private_key'),
    'url_endpoint' => config('services.imagekit.url_endpoint'),
]);

        // Debug: Log what we're receiving
        \Log::info('Media upload request received', [
            'all_data' => $request->all(),
            'files' => $request->file('files'),
            'files_array' => $request->file('files[]'),
            'has_files' => $request->hasFile('files'),
            'has_files_array' => $request->hasFile('files[]'),
        ]);

        // Determine which format we have and validate accordingly
        $files = null;
        $validator = null;

        if ($request->hasFile('files') && is_array($request->file('files'))) {
            // We have 'files' format
            $files = $request->file('files');
            $validator = Validator::make($request->all(), [
                'files' => 'required|array|max:4',
                'files.*' => 'required|file|mimes:jpeg,png,jpg,gif,mp4,mov,avi,webm|max:102400', // 100MB max
                'folder' => 'nullable|string|max:255',
            ]);
        } elseif ($request->hasFile('files[]') && is_array($request->file('files[]'))) {
            // We have 'files[]' format
            $files = $request->file('files[]');
            $validator = Validator::make($request->all(), [
                'files[]' => 'required|array|max:4',
                'files.*' => 'required|file|mimes:jpeg,png,jpg,gif,mp4,mov,avi,webm|max:102400', // 100MB max
                'folder' => 'nullable|string|max:255',
            ]);
        } else {
            // No valid files found
            return response()->json([
                'success' => false,
                'message' => 'No valid files found. Please provide files in either "files" or "files[]" format.',
                'received' => [
                    'has_files' => $request->hasFile('files'),
                    'has_files_array' => $request->hasFile('files[]'),
                    'files_count' => $request->file('files') ? count($request->file('files')) : 0,
                    'files_array_count' => $request->file('files[]') ? count($request->file('files[]')) : 0,
                ]
            ], 422);
        }

        if ($validator->fails()) {
            \Log::error('Media upload validation failed', [
                'errors' => $validator->errors(),
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $imageKitService = new ImageKitService();
            $folder = $request->input('folder', 'posts');
            $uploadedFiles = [];
            $failedFiles = [];

            if (!$files || !is_array($files)) {
                throw new \Exception('No valid files found in request');
            }

            foreach ($files as $file) {
                $uploadResult = $imageKitService->uploadFile($file, $folder);

                if ($uploadResult['success']) {
                    $uploadedFiles[] = [
                        'original_name' => $file->getClientOriginalName(),
                        'url' => $uploadResult['url'],
                        'thumbnail_url' => $uploadResult['thumbnail_url'],
                        'file_id' => $uploadResult['file_id'],
                        'size' => $uploadResult['size'],
                        'mime_type' => $uploadResult['mime_type'],
                        'width' => $uploadResult['width'],
                        'height' => $uploadResult['height'],
                    ];
                } else {
                    $failedFiles[] = [
                        'original_name' => $file->getClientOriginalName(),
                        'error' => $uploadResult['error'],
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Media upload completed',
                'data' => [
                    'uploaded_files' => $uploadedFiles,
                    'failed_files' => $failedFiles,
                    'total_uploaded' => count($uploadedFiles),
                    'total_failed' => count($failedFiles),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Upload failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete media file
     */
    public function delete(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
           
            $imageKitService = new ImageKitService();
            $deleted = $imageKitService->deleteMediaFile($request->file_id);

            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'File deleted successfully',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete file',
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Delete failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get file metadata
     */
    public function metadata(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $imageKitService = new ImageKitService();
            $metadata = $imageKitService->getFileMetadata($request->file_id);

            if ($metadata) {
                return response()->json([
                    'success' => true,
                    'data' => $metadata,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'File not found or metadata unavailable',
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Metadata fetch failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get optimized URL with transformations
     */
    public function optimizedUrl(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'url' => 'required|url',
            'width' => 'nullable|integer|min:1|max:2000',
            'height' => 'nullable|integer|min:1|max:2000',
            'quality' => 'nullable|integer|min:1|max:100',
            'format' => 'nullable|string|in:auto,jpg,png,webp',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $imageKitService = new ImageKitService();

            $transformations = [];
            if ($request->width) $transformations['w'] = $request->width;
            if ($request->height) $transformations['h'] = $request->height;
            if ($request->quality) $transformations['q'] = $request->quality;
            if ($request->format) $transformations['f'] = $request->format;

            $optimizedUrl = $imageKitService->getOptimizedUrl($request->url, $transformations);

            return response()->json([
                'success' => true,
                'data' => [
                    'original_url' => $request->url,
                    'optimized_url' => $optimizedUrl,
                    'transformations' => $transformations,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'URL optimization failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
