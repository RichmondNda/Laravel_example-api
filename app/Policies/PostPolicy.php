<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PostPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool
    {
        // Everyone can view posts (even guests)
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Post $post): bool
    {
        // Everyone can view a post
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // All authenticated users can create posts
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Post $post): bool
    {
        // Only the author can update their post
        return $user->id === $post->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Post $post): bool
    {
        // Only the author can delete their post
        return $user->id === $post->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Post $post): bool
    {
        // Only the author can restore their post
        return $user->id === $post->user_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Post $post): bool
    {
        // Only the author can force delete their post
        return $user->id === $post->user_id;
    }
}
