<?php

namespace App\Http\Controllers\Api;

use App\Data\PostData;
use App\Data\StorePostData;
use App\Data\UpdatePostData;
use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Services\PostService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Spatie\LaravelData\PaginatedDataCollection;

class PostController extends Controller
{
    use AuthorizesRequests;
    

    public function __construct(
        protected PostService $postService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(): PaginatedDataCollection
    {
        return $this->postService->getAllPosts(15);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePostData $data): JsonResponse
    {
        $post = $this->postService->createPost($data);
        
        return response()->json($post, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post): PostData
    {
        return $this->postService->getPost($post);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePostData $data, Post $post): PostData
    {
        $this->authorize('update', $post);
        
        return $this->postService->updatePost($post, $data);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post): JsonResponse
    {
        $this->authorize('delete', $post);
        
        $this->postService->deletePost($post);
        
        return response()->json([
            'success' => true,
            'message' => 'Post deleted successfully',
        ], 200);
    }
}
