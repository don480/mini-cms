<?php

use App\Models\Post;

beforeEach(function () {
    $this->withoutVite();
});

it('lists published posts in a paginated resource', function () {
    Post::factory()->published()->create();
    Post::factory()->create(['is_published' => false]);

    $this->withHeader('Accept', 'application/json')
        ->get('/api/posts')
        ->assertOk()
        ->assertJsonStructure(['data', 'links', 'meta'])
        ->assertJsonMissingPath('data.0.email')
        ->assertJsonMissingPath('data.0.role')
        ->assertJsonMissingPath('data.0.user_id');
});

it('shows a published post and forbids a draft', function () {
    $published = Post::factory()->published()->create();
    $draft = Post::factory()->create(['is_published' => false]);

    $this->withHeader('Accept', 'application/json')
        ->get("/api/posts/{$published->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $published->id)
        ->assertJsonStructure(['data' => ['body', 'tags']]);

    $this->getJson("/api/posts/{$draft->id}")
        ->assertForbidden();
});

it('validates per_page', function () {
    $this->withHeader('Accept', 'application/json')
        ->get('/api/posts?per_page=500')
        ->assertUnprocessable()
        ->assertJsonValidationErrors('per_page');
});
