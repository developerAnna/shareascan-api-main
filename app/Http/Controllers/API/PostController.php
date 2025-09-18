<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Models\PostMedia;
use App\Models\PostLike;
use App\Models\PostRepost;
use App\Services\ImageKitService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PostController extends Controller
{
    /**
     * Display a listing of posts (timeline)
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $perPage = $request->get('per_page', 20);
        $page = $request->get('page', 1);

        $query = Post::with(['user', 'media', 'likes', 'reposts'])
            ->published()
            ->public()
            ->notReply()
            ->notQuote()
            ->orderBy('published_at', 'desc');

        // Get posts from users the current user follows
        if ($user) {
            $followingIds = $user->following()->pluck('following_id')->toArray();
            $followingIds[] = $user->id; // Include user's own posts

            $query->whereIn('user_id', $followingIds);
        }

        $posts = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'success' => true,
            'data' => PostResource::collection($posts->items()),
            'pagination' => [
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total(),
            ],
        ]);
    }

    /**
     * Store a newly created post
     */
    public function store(Request $request): JsonResponse
    {
        // Log the incoming request
        \Log::info('Post creation request received', [
            'request_data' => $request->all(),
            'headers' => $request->headers->all(),
            'user_agent' => $request->userAgent(),
            'ip' => $request->ip(),
        ]);

        $validator = Validator::make($request->all(), [
            'content' => 'required|string|max:280',
            'visibility' => 'in:public,followers,private',
            'location' => 'nullable|string|max:255',
            'scheduled_at' => 'nullable|date|after:now',
            'media' => 'nullable|array|max:4', // Max 4 media files
            'media.*.file_id' => 'required_with:media|string',
            'media.*.url' => 'required_with:media|string|url',
            'media.*.thumbnail_url' => 'required_with:media|string|url',
            'media.*.alt_text' => 'nullable|string|max:255',
            'media.*.caption' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            \Log::error('Post validation failed', [
                'errors' => $validator->errors(),
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        \Log::info('Post validation passed, proceeding with creation');

        try {
            \Log::info('Starting database transaction for post creation');
            DB::beginTransaction();

            // Get the authenticated user
            $user = Auth::user();
            $userId = $user ? $user->id : null;

            if (!$userId) {
                throw new \Exception('User not authenticated');
            }

            \Log::info('Creating post with data', [
                'user_id' => $userId,
                'content' => $request->content,
                'type' => $request->has('media') && count($request->media) > 0 ? 'media' : 'text',
                'visibility' => $request->visibility ?? 'public',
                'location' => $request->location,
                'scheduled_at' => $request->scheduled_at,
                'status' => $request->scheduled_at ? 'scheduled' : 'published',
                'published_at' => $request->scheduled_at ? null : now(),
            ]);

            $post = Post::create([
                'user_id' => $userId,
                'content' => $request->content,
                'type' => $request->has('media') && count($request->media) > 0 ? 'media' : 'text',
                'visibility' => $request->visibility ?? 'public',
                'location' => $request->location,
                'scheduled_at' => $request->scheduled_at,
                'status' => $request->scheduled_at ? 'scheduled' : 'published',
                'published_at' => $request->scheduled_at ? null : now(),
            ]);

            \Log::info('Post created successfully', [
                'post_id' => $post->id,
                'post_data' => $post->toArray(),
            ]);

            // Generate QR code for public posts
            if ($post->visibility === 'public' && $post->status === 'published') {
                $this->generatePostQrCode($post);
            }

            // Handle media (already uploaded to ImageKit)
            if ($request->has('media') && is_array($request->media)) {
                \Log::info('Processing media for post', [
                    'media_count' => count($request->media),
                    'media_data' => $request->media,
                ]);

                foreach ($request->media as $index => $mediaData) {
                    \Log::info('Creating media record', [
                        'index' => $index,
                        'media_data' => $mediaData,
                        'post_id' => $post->id,
                    ]);

                    // Create media record using already uploaded data
                    $media = PostMedia::create([
                        'post_id' => $post->id,
                        'media_type' => $this->getMediaTypeFromUrl($mediaData['url']),
                        'media_url' => $mediaData['url'],
                        'thumbnail_url' => $mediaData['thumbnail_url'],
                        'alt_text' => $mediaData['alt_text'] ?? null,
                        'caption' => $mediaData['caption'] ?? null,
                        'file_size' => 0, // We don't have this info from ImageKit
                        'width' => null, // We don't have this info from ImageKit
                        'height' => null, // We don't have this info from ImageKit
                        'order' => $index,
                        'is_processed' => true,
                    ]);

                    \Log::info('Media record created successfully', [
                        'media_id' => $media->id,
                        'media_data' => $media->toArray(),
                    ]);
                }
            } else {
                \Log::info('No media to process for this post');
            }

            \Log::info('Committing database transaction');
            DB::commit();

            $post->load(['user', 'media']);

            \Log::info('Post creation completed successfully', [
                'post_id' => $post->id,
                'final_post_data' => $post->toArray(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Post created successfully',
                'data' => new PostResource($post),
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Post creation failed with exception', [
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'error_trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create post',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified post
     */
    public function show($id): JsonResponse
    {
        $post = Post::with(['user', 'media', 'likes', 'reposts', 'replies.user', 'quotes.user'])
            ->published()
            ->findOrFail($id);

        $user = Auth::user();

        if (!$post->canBeViewedBy($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Post not accessible',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => new PostResource($post),
        ]);
    }

    /**
     * Update the specified post
     */
    public function update(Request $request, $id): JsonResponse
    {
        $post = Post::findOrFail($id);
        $user = Auth::user();

        if (!$post->isOwnedBy($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to edit this post',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'content' => 'required|string|max:280',
            'visibility' => 'in:public,followers,private',
            'location' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $post->update([
            'content' => $request->content,
            'visibility' => $request->visibility,
            'location' => $request->location,
        ]);

        $post->load(['user', 'media']);

        return response()->json([
            'success' => true,
            'message' => 'Post updated successfully',
            'data' => new PostResource($post),
        ]);
    }

    /**
     * Remove the specified post
     */
    public function destroy($id): JsonResponse
    {
        $post = Post::findOrFail($id);
        $user = Auth::user();

        if (!$post->isOwnedBy($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to delete this post',
            ], 403);
        }

        $post->delete();

        return response()->json([
            'success' => true,
            'message' => 'Post deleted successfully',
        ]);
    }

    /**
     * Like/unlike a post
     */
    public function toggleLike($id): JsonResponse
    {
        $post = Post::published()->findOrFail($id);
        $user = Auth::user();

        if (!$post->canBeViewedBy($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Post not accessible',
            ], 403);
        }

        $isLiked = PostLike::toggleLike($user->id, $post->id);

        return response()->json([
            'success' => true,
            'message' => $isLiked ? 'Post liked' : 'Post unliked',
            'data' => [
                'is_liked' => $isLiked,
                'likes_count' => $post->fresh()->likes_count,
            ],
        ]);
    }

    /**
     * Repost/unrepost a post
     */
    public function toggleRepost(Request $request, $id): JsonResponse
    {
        $post = Post::published()->findOrFail($id);
        $user = Auth::user();

        if (!$post->canBeViewedBy($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Post not accessible',
            ], 403);
        }

        $repostText = $request->input('repost_text');
        $isReposted = PostRepost::toggleRepost($user->id, $post->id, $repostText);

        return response()->json([
            'success' => true,
            'message' => $isReposted ? 'Post reposted' : 'Repost removed',
            'data' => [
                'is_reposted' => $isReposted,
                'reposts_count' => $post->fresh()->reposts_count,
            ],
        ]);
    }

    /**
     * Get user's posts
     */
    public function userPosts($userId, Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 20);
        $page = $request->get('page', 1);

        $query = Post::with(['user', 'media', 'likes', 'reposts'])
            ->where('user_id', $userId)
            ->published()
            ->notReply()
            ->notQuote()
            ->orderBy('published_at', 'desc');

        $posts = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'success' => true,
            'data' => PostResource::collection($posts->items()),
            'pagination' => [
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total(),
            ],
        ]);
    }

    /**
     * Get authenticated user's own posts (all visibility levels)
     */
    public function myPosts(Request $request): JsonResponse
    {
        $user = Auth::user();
        $perPage = $request->get('per_page', 20);
        $page = $request->get('page', 1);

        \Log::info('MyPosts request', [
            'user_id' => $user ? $user->id : null,
            'user_email' => $user ? $user->email : null,
        ]);

        $query = Post::with(['user', 'media', 'likes', 'reposts'])
            ->where('user_id', $user->id)
            ->published()
            ->notReply()
            ->notQuote()
            ->orderBy('published_at', 'desc');

        \Log::info('MyPosts SQL Query', [
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings(),
        ]);

        $posts = $query->paginate($perPage, ['*'], 'page', $page);

        \Log::info('MyPosts results', [
            'total_posts' => $posts->total(),
            'posts_on_page' => count($posts->items()),
            'posts_data' => $posts->items(),
        ]);

        // Add public URLs for each post
        $postsData = $posts->items();
        foreach ($postsData as $post) {
            // Add public URL for posts that are public
            if ($post->visibility === 'public') {
                $post->public_url = env('FRONTEND_URL', 'http://localhost:5900') . "/users/{$post->user_id}/posts/{$post->id}";
            }
        }

        return response()->json([
            'success' => true,
            'data' => PostResource::collection($postsData),
            'pagination' => [
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total(),
            ],
        ]);
    }

    /**
     * Get post replies
     */
    public function replies($id, Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 20);
        $page = $request->get('page', 1);

        $replies = Post::with(['user', 'media', 'likes', 'reposts'])
            ->where('parent_post_id', $id)
            ->published()
            ->orderBy('published_at', 'asc')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'success' => true,
            'data' => PostResource::collection($replies->items()),
            'pagination' => [
                'current_page' => $replies->currentPage(),
                'last_page' => $replies->lastPage(),
                'per_page' => $replies->perPage(),
                'total' => $replies->total(),
            ],
        ]);
    }

    /**
     * Helper method to determine media type
     */
    private function getMediaType(string $mimeType): string
    {
        if (Str::startsWith($mimeType, 'image/')) {
            return 'image';
        }

        if (Str::startsWith($mimeType, 'video/')) {
            return 'video';
        }

        return 'image'; // Default fallback
    }

    /**
     * Public view of a single post (no authentication required)
     */
    public function publicShow($userId, $postId): JsonResponse
    {
        $post = Post::with(['user', 'media', 'likes', 'reposts', 'replies.user', 'quotes.user'])
            ->where('user_id', $userId)
            ->where('id', $postId)
            ->published()
            ->public()
            ->first();

        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found or not accessible',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new PostResource($post),
        ]);
    }

    /**
     * Get public posts by a specific user (no authentication required)
     */
    public function publicUserPosts($userId, Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 20);
        $page = $request->get('page', 1);

        $query = Post::with(['user', 'media', 'likes', 'reposts'])
            ->where('user_id', $userId)
            ->published()
            ->public()
            ->notReply()
            ->notQuote()
            ->orderBy('published_at', 'desc');

        $posts = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'success' => true,
            'data' => PostResource::collection($posts->items()),
            'pagination' => [
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total(),
            ],
        ]);
    }

    /**
     * Generate QR code for a post
     */
    private function generatePostQrCode(Post $post): void
    {
        try {
            // Generate the public URL for the post
            $postUrl = env('FRONTEND_URL', 'http://localhost:5900') . "/users/{$post->user_id}/posts/{$post->id}";

            // Generate QR code filename
            $filename = "qr-codes/posts/post_{$post->id}_" . time() . '.png';

            // Generate QR code using the existing helper function
            $qrCodeData = generateQR('#000000', [0, 0, 0], $postUrl, 'post');

            // Update post with QR code path
            $post->update(['qr_code_path' => $filename]);

            \Log::info('QR Code generated successfully for post', [
                'post_id' => $post->id,
                'qr_code_path' => $filename,
                'post_url' => $postUrl,
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to generate QR code for post', [
                'post_id' => $post->id,
                'error' => $e->getMessage(),
            ]);
            // Don't throw exception - QR code generation failure shouldn't stop post creation
        }
    }

    /**
     * Helper method to determine media type from URL
     */
    private function getMediaTypeFromUrl(string $url): string
    {
        if (Str::endsWith($url, '.jpg') || Str::endsWith($url, '.jpeg') || Str::endsWith($url, '.png') || Str::endsWith($url, '.gif')) {
            return 'image';
        }

        if (Str::endsWith($url, '.mp4') || Str::endsWith($url, '.mov') || Str::endsWith($url, '.avi') || Str::endsWith($url, '.webm')) {
            return 'video';
        }

        return 'image'; // Default fallback
    }
}
