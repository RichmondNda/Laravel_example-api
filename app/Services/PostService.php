<?php

namespace App\Services;

use App\Data\PostData;
use App\Data\StorePostData;
use App\Data\UpdatePostData;
use App\Models\Post;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\PaginatedDataCollection;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;

class PostService
{
    /**
     * Get all posts with pagination, filters, search and sorting.
     *
     * @param int $perPage
     * @return PaginatedDataCollection
     */
    public function getAllPosts(int $perPage = 15): PaginatedDataCollection
    {
        $cacheKey = 'posts:' . request()->getQueryString() . ':page:' . request()->get('page', 1);
        
        $posts = cache()->remember($cacheKey, 300, function () use ($perPage) {
            return QueryBuilder::for(Post::class)
                ->allowedFilters([
                    AllowedFilter::exact('user_id'),
                    AllowedFilter::scope('published'),
                    AllowedFilter::partial('title'),
                    AllowedFilter::partial('content'),
                    'published_at',
                ])
                ->allowedSorts([
                    'created_at',
                    'updated_at',
                    'published_at',
                    'title',
                    AllowedSort::field('user', 'user_id'),
                ])
                ->allowedIncludes(['user'])
                ->with('user')
                ->latest()
                ->paginate($perPage);
        });

        return PostData::collect($posts, PaginatedDataCollection::class);
    }

    /**
     * Get a single post by ID.
     *
     * @param Post $post
     * @return PostData
     */
    public function getPost(Post $post): PostData
    {
        return PostData::fromModel($post->load('user'));
    }

    /**
     * Create a new post.
     *
     * @param StorePostData $data
     * @return PostData
     */
    public function createPost(StorePostData $data): PostData
    {
        $post = Post::create($data->toArray());
        
        // Clear posts cache
        $this->clearPostsCache();
        
        return PostData::fromModel($post->load('user'));
    }

    /**
     * Update an existing post.
     *
     * @param Post $post
     * @param UpdatePostData $data
     * @return PostData
     */
    public function updatePost(Post $post, UpdatePostData $data): PostData
    {
        $post->update($data->toArray());
        
        // Clear posts cache
        $this->clearPostsCache();
        
        return PostData::fromModel($post->fresh(['user']));
    }

    /**
     * Delete a post.
     *
     * @param Post $post
     * @return bool
     */
    public function deletePost(Post $post): bool
    {
        $deleted = $post->delete();
        
        // Clear posts cache
        $this->clearPostsCache();
        
        return $deleted;
    }

    /**
     * Clear all posts cache.
     *
     * @return void
     */
    protected function clearPostsCache(): void
    {
        cache()->flush(); // In production, use cache tags or more specific keys
    }

    /**
     * Get posts by user.
     *
     * @param int $userId
     * @param int $perPage
     * @return PaginatedDataCollection
     */
    public function getPostsByUser(int $userId, int $perPage = 15): PaginatedDataCollection
    {
        $posts = Post::with('user')
            ->where('user_id', $userId)
            ->latest()
            ->paginate($perPage);

        return PostData::collect($posts, PaginatedDataCollection::class);
    }

    /**
     * Get published posts only.
     *
     * @param int $perPage
     * @return PaginatedDataCollection
     */
    public function getPublishedPosts(int $perPage = 15): PaginatedDataCollection
    {
        $posts = Post::with('user')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->latest('published_at')
            ->paginate($perPage);

        return PostData::collect($posts, PaginatedDataCollection::class);
    }
}
