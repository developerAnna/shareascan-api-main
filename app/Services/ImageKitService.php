<?php

namespace App\Services;

use ImageKit\ImageKit;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ImageKitService
{
    private ImageKit $imageKit;

    public function __construct()
    {
        $this->imageKit = new ImageKit(
            config('services.imagekit.public_key'),
            config('services.imagekit.private_key'),
            config('services.imagekit.url_endpoint')
        );
    }

    /**
     * Upload a file to ImageKit
     */
    public function uploadFile(UploadedFile $file, string $folder = 'posts'): array
    {
        try {
            // Generate unique filename
            $filename = $this->generateUniqueFilename($file);

            // Log the upload attempt
            Log::info('ImageKit upload attempt', [
                'filename' => $filename,
                'folder' => $folder,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'file_path' => $file->getRealPath(),
                'original_name' => $file->getClientOriginalName(),
            ]);

            // Use the official ImageKit SDK
            $uploadResponse = $this->imageKit->uploadFile([
                'file' => base64_encode(file_get_contents($file->getRealPath())), // Send file content as base64
                'fileName' => $filename,
                'folder' => $folder,
                'useUniqueFileName' => false,
                'tags' => ['shareascan', 'post'],
                'responseFields' => ['tags', 'metadata', 'versionId'],
            ]);

            if ($uploadResponse->result) {
                $data = $uploadResponse->result;

                Log::info('ImageKit upload successful', [
                    'file_id' => $data->fileId,
                    'url' => $data->url,
                    'response_data' => $data,
                ]);

                return [
                    'success' => true,
                    'url' => $data->url,
                    'file_id' => $data->fileId,
                    'filename' => $filename,
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'width' => $data->width ?? null,
                    'height' => $data->height ?? null,
                    'thumbnail_url' => $this->getThumbnailUrl($data->url),
                ];
            }

            Log::error('ImageKit upload failed', [
                'response' => $uploadResponse,
                'error' => $uploadResponse->error ?? 'Unknown error',
            ]);

            return [
                'success' => false,
                'error' => 'Upload failed: ' . ($uploadResponse->error->message ?? 'Unknown error'),
            ];

        } catch (\Exception $e) {
            Log::error('ImageKit upload exception', [
                'error' => $e->getMessage(),
                'file' => $file->getClientOriginalName(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => 'Upload exception: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get optimized URL with transformations
     */
    public function getOptimizedUrl(string $originalUrl, array $transformations = []): string
    {
        if (empty($transformations)) {
            return $originalUrl;
        }

        $queryParams = [];

        foreach ($transformations as $key => $value) {
            if (is_array($value)) {
                $queryParams[] = $key . '-' . implode('-', $value);
            } else {
                $queryParams[] = $key . '-' . $value;
            }
        }

        $separator = str_contains($originalUrl, '?') ? '&' : '?';
        return $originalUrl . $separator . 'tr=' . implode(',', $queryParams);
    }

    /**
     * Get thumbnail URL
     */
    public function getThumbnailUrl(string $originalUrl, int $width = 300, int $height = 300): string
    {
        return $this->getOptimizedUrl($originalUrl, [
            'w' => $width,
            'h' => $height,
            'c' => 'at_max',
            'fo' => 'auto',
        ]);
    }

    /**
     * Get video thumbnail
     */
    public function getVideoThumbnail(string $videoUrl, int $width = 300, int $height = 300): string
    {
        return $this->getOptimizedUrl($videoUrl, [
            'w' => $width,
            'h' => $height,
            'c' => 'at_max',
            'fo' => 'auto',
            't' => 'video',
        ]);
    }

    /**
     * Delete file from ImageKit
     */
    public function deleteMediaFile(string $fileId): bool
    {
        try {
            $response = $this->imageKit->deleteFile($fileId);
            // return $response->result !== null;
            if (isset($response->responseMetadata['statusCode']) && $response->responseMetadata['statusCode'] === 204 && $response->error === null
            ) 
            {
                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('ImageKit delete failed', [
                'file_id' => $fileId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Get file metadata
     */
    public function getFileMetadata(string $fileId): ?array
    {
        try {
            $response = $this->imageKit->getFileDetails($fileId);


            if ($response->result) {
                return (array) $response->result;
            }
        } catch (\Exception $e) {
            Log::error('ImageKit metadata fetch failed', [
                'file_id' => $fileId,
                'error' => $e->getMessage(),
            ]);
        }

        return null;
    }

    /**
     * Generate unique filename
     */
    private function generateUniqueFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $timestamp = now()->timestamp;
        $random = Str::random(8);

        return "post_{$timestamp}_{$random}.{$extension}";
    }

    /**
     * Get CDN URL for a file
     */
    public function getCdnUrl(string $filePath): string
    {
        return rtrim(config('services.imagekit.url_endpoint'), '/') . '/' . ltrim($filePath, '/');
    }

    /**
     * Process video and extract metadata
     */
    public function processVideo(string $videoUrl): array
    {
        // For now, return basic info
        // In production, you might want to use a video processing service
        return [
            'duration' => null,
            'width' => null,
            'height' => null,
            'thumbnail_url' => $this->getVideoThumbnail($videoUrl),
        ];
    }

    /**
     * Get file info from URL
     */
    public function getFileInfoFromUrl(string $url): array
    {
        $path = parse_url($url, PHP_URL_PATH);
        $filename = basename($path);

        return [
            'filename' => $filename,
            'extension' => pathinfo($filename, PATHINFO_EXTENSION),
            'path' => $path,
        ];
    }
}
