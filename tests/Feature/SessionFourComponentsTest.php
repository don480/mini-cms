<?php

use App\Models\Post;

beforeEach(function () {
    $this->withoutVite();
});

test('home renders the shared layout and success alert', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('Bienvenue sur Mini-CMS')
        ->assertSee('aria-current="page"', false);
});

test('about renders both alerts and its page header subtitle', function () {
    $this->get('/a-propos')
        ->assertOk()
        ->assertSee('Le projet Mini-CMS et ses objectifs.')
        ->assertSee('Projet pédagogique')
        ->assertSee('Données en base')
        ->assertSee('heni anas')
        ->assertSee('aria-current="page"', false);
});

test('article index renders four cards and marks the articles navigation active', function () {
    $posts = [];

    foreach (range(1, 4) as $number) {
        $posts[] = Post::create([
            'title' => 'Article '.$number,
            'slug' => 'article-'.$number,
            'body' => 'Contenu de test pour cet article.',
            'is_published' => $number % 2 === 0,
            'published_at' => $number % 2 === 0 ? now() : null,
        ]);
    }

    $response = $this->get('/posts')
        ->assertOk()
        ->assertSee('Article 1')
        ->assertSee('Brouillon')
        ->assertSee(route('posts.show', $posts[0]))
        ->assertSee('aria-current="page"', false);

    expect(substr_count($response->getContent(), '<article'))->toBe(4);
});

test('empty archive renders the shared alert and keeps articles navigation active', function () {
    $this->get('/posts/archive/2025')
        ->assertOk()
        ->assertSee('Articles de 2025')
        ->assertSee('Aucun article')
        ->assertSee('aria-current="page"', false);
});

test('article detail renders all body paragraphs and unknown slugs return 404', function () {
    $post = Post::create([
        'title' => 'Article de détail',
        'slug' => 'article-de-detail',
        'body' => 'Contenu complet pour le détail de cet article.',
        'is_published' => true,
        'published_at' => now(),
    ]);

    $this->get(route('posts.show', $post))
        ->assertOk()
        ->assertSee($post->body)
        ->assertSee(route('posts.index'))
        ->assertSee('aria-current="page"', false);

    $this->get('/posts/999')->assertNotFound();
});
