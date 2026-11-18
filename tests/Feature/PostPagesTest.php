<?php

use App\Models\Post;

beforeEach(function () {
    $this->withoutVite();
});

it('shows the posts list', function () {
    Post::create([
        'title' => 'Premier article',
        'slug' => 'premier-article',
        'body' => 'Contenu du premier article de test.',
        'is_published' => true,
        'published_at' => now(),
    ]);

    $this->get(route('posts.index'))
        ->assertOk()
        ->assertSee('Premier article');
});

it('shows a message when there is no post', function () {
    $this->get(route('posts.index'))
        ->assertOk()
        ->assertSee('Aucun article');
});

it('shows a single post through model route binding', function () {
    $post = Post::create([
        'title' => 'Article complet',
        'slug' => 'article-complet',
        'body' => 'Texte complet de cet article de test.',
        'is_published' => true,
        'published_at' => now(),
    ]);

    $this->get(route('posts.show', $post))
        ->assertOk()
        ->assertSee('Texte complet de cet article de test.');
});

it('returns 404 for an unknown post', function () {
    $this->get('/posts/999')->assertNotFound();
});

it('shows the create form', function () {
    $this->get(route('posts.create'))
        ->assertOk()
        ->assertSee('Nouvel article');
});

it('validates required fields and preserves entered values', function () {
    $this->from(route('posts.create'))
        ->post(route('posts.store'), [
            'title' => 'Titre conservé',
            'slug' => '',
            'body' => '',
        ])
        ->assertSessionHasErrors(['slug', 'body'])
        ->assertSessionHas('_old_input.title', 'Titre conservé');
});

it('rejects duplicate slugs', function () {
    Post::create([
        'title' => 'Article existant',
        'slug' => 'slug-existant',
        'body' => 'Contenu assez long pour valider cet article.',
    ]);

    $this->from(route('posts.create'))
        ->post(route('posts.store'), [
            'title' => 'Nouvel article',
            'slug' => 'slug-existant',
            'body' => 'Contenu assez long pour valider cet article.',
        ])
        ->assertRedirect(route('posts.create'))
        ->assertSessionHasErrors('slug');
});

it('stores published and draft posts and redirects to their details', function () {
    $publishedResponse = $this->post(route('posts.store'), [
        'title' => 'Article publié depuis le formulaire',
        'slug' => 'article-publie-formulaire',
        'body' => 'Ce contenu dépasse bien les vingt caractères demandés.',
        'is_published' => '1',
    ]);

    $publishedPost = Post::where('slug', 'article-publie-formulaire')->firstOrFail();

    $publishedResponse->assertRedirect(route('posts.show', $publishedPost));
    expect($publishedPost->is_published)->toBeTrue()
        ->and($publishedPost->published_at)->not->toBeNull();

    $draftResponse = $this->post(route('posts.store'), [
        'title' => 'Article brouillon depuis le formulaire',
        'slug' => 'article-brouillon-formulaire',
        'body' => 'Ce brouillon contient aussi plus de vingt caractères.',
    ]);

    $draftPost = Post::where('slug', 'article-brouillon-formulaire')->firstOrFail();

    $draftResponse->assertRedirect(route('posts.show', $draftPost));
    expect($draftPost->is_published)->toBeFalse()
        ->and($draftPost->published_at)->toBeNull();
});
