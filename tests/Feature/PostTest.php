<?php

use App\Models\Post;
use App\Models\User;

test('can list posts', function () {
    Post::factory()->count(5)->create();

    $response = $this->getJson('/api/posts');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'content',
                    'user',
                ]
            ],
            'links',
            'meta',
        ]);
});

test('can view a single post', function () {
    $post = Post::factory()->create();

    $response = $this->getJson("/api/posts/{$post->id}");

    $response->assertStatus(200)
        ->assertJson([
            'id' => $post->id,
            'title' => $post->title,
        ]);
});

test('authenticated user can create post', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $response = $this->postJson('/api/posts', [
        'title' => 'Test Post',
        'content' => 'This is a test post content that is long enough.',
        'user_id' => $user->id,
    ], [
        'Authorization' => 'Bearer ' . $token,
    ]);

    $response->assertStatus(201)
        ->assertJson([
            'title' => 'Test Post',
        ]);

    $this->assertDatabaseHas('posts', [
        'title' => 'Test Post',
    ]);
});

test('cannot create post without authentication', function () {
    $user = User::factory()->create();

    $response = $this->postJson('/api/posts', [
        'title' => 'Test Post',
        'content' => 'This is a test post content.',
        'user_id' => $user->id,
    ]);

    $response->assertStatus(401);
});

test('user can update their own post', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create(['user_id' => $user->id]);
    $token = $user->createToken('test-token')->plainTextToken;

    $response = $this->putJson("/api/posts/{$post->id}", [
        'title' => 'Updated Title',
    ], [
        'Authorization' => 'Bearer ' . $token,
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'title' => 'Updated Title',
        ]);
});

test('user cannot update another user post', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $post = Post::factory()->create(['user_id' => $user1->id]);
    $token = $user2->createToken('test-token')->plainTextToken;

    $response = $this->putJson("/api/posts/{$post->id}", [
        'title' => 'Updated Title',
    ], [
        'Authorization' => 'Bearer ' . $token,
    ]);

    $response->assertStatus(403);
});

test('user can delete their own post', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create(['user_id' => $user->id]);
    $token = $user->createToken('test-token')->plainTextToken;

    $response = $this->deleteJson("/api/posts/{$post->id}", [], [
        'Authorization' => 'Bearer ' . $token,
    ]);

    $response->assertStatus(200);
    $this->assertSoftDeleted('posts', ['id' => $post->id]);
});

test('can filter posts by user', function () {
    $user = User::factory()->create();
    Post::factory()->count(3)->create(['user_id' => $user->id]);
    Post::factory()->count(2)->create();

    $response = $this->getJson("/api/posts?filter[user_id]={$user->id}");

    $response->assertStatus(200);
    expect($response->json('data'))->toHaveCount(3);
});

test('can search posts by title', function () {
    Post::factory()->create(['title' => 'Laravel Tutorial']);
    Post::factory()->create(['title' => 'PHP Guide']);
    Post::factory()->create(['title' => 'Laravel Advanced']);

    $response = $this->getJson('/api/posts?filter[title]=Laravel');

    $response->assertStatus(200);
    expect($response->json('data'))->toHaveCount(2);
});

test('can sort posts by created_at', function () {
    Post::factory()->count(3)->create();

    $response = $this->getJson('/api/posts?sort=-created_at');

    $response->assertStatus(200);
});
