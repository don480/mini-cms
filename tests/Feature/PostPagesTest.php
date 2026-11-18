<?php

use App\Models\Post;
use Illuminate\Support\Str;

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

it('shows only published posts in the archive', function () {
    Post::create([
        'title' => 'Article publié',
        'slug' => 'article-publie-archive',
        'body' => 'Contenu publié pour tester l’archive.',
        'is_published' => true,
        'published_at' => now(),
    ]);

    Post::create([
        'title' => 'Article brouillon',
        'slug' => 'article-brouillon-archive',
        'body' => 'Contenu brouillon à exclure de l’archive.',
    ]);

    $this->get(route('posts.archive', now()->year))
        ->assertOk()
        ->assertSee('Article publié')
        ->assertDontSee('Article brouillon');
});

it('shows a manual excerpt or truncated body on post cards', function () {
    Post::create([
        'title' => 'Article avec extrait',
        'slug' => 'article-avec-extrait',
        'excerpt' => 'Extrait rédigé à la main.',
        'body' => 'Texte complet de l’article avec un extrait.',
    ]);

    $fallbackBody = str_repeat('Texte de remplacement pour la carte. ', 6);

    Post::create([
        'title' => 'Article sans extrait',
        'slug' => 'article-sans-extrait',
        'body' => $fallbackBody,
    ]);

    $this->get(route('posts.index'))
        ->assertOk()
        ->assertSee('Extrait rédigé à la main.')
        ->assertSee(Str::limit($fallbackBody, 120));
});

it('shows the create form', function () {
    $this->get(route('posts.create'))
        ->assertOk()
        ->assertSee('Nouvel article');
});

it('shows only the three most recently published posts on the home page', function () {
    foreach ([
        ['Article publie ancien', 'article-publie-ancien', now()->subDays(4)],
        ['Article publie recent 1', 'article-publie-recent-1', now()->subDays(3)],
        ['Article publie recent 2', 'article-publie-recent-2', now()->subDays(2)],
        ['Article publie recent 3', 'article-publie-recent-3', now()->subDay()],
    ] as [$title, $slug, $publishedAt]) {
        Post::create([
            'title' => $title,
            'slug' => $slug,
            'body' => 'Contenu publié suffisamment long pour cet article.',
            'is_published' => true,
            'published_at' => $publishedAt,
        ]);
    }

    Post::create([
        'title' => 'Article brouillon récent',
        'slug' => 'article-brouillon-recent',
        'body' => 'Ce brouillon ne doit pas apparaitre sur la page accueil.',
        'is_published' => false,
        'published_at' => now(),
    ]);

    $response = $this->get(route('home'))
        ->assertOk()
        ->assertSeeInOrder([
            'Article publie recent 3',
            'Article publie recent 2',
            'Article publie recent 1',
        ])
        ->assertDontSee('Article publie ancien')
        ->assertDontSee('Article brouillon récent');

    expect(substr_count($response->getContent(), '<article'))->toBe(3);
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
