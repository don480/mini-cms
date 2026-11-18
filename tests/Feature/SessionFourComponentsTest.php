<?php

test('home renders the shared layout and success alert', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('Bienvenue sur Mini-CMS')
        ->assertSee('Sprint en cours')
        ->assertSee('aria-current="page"', false);
});

test('about renders both alerts and its page header subtitle', function () {
    $this->get('/a-propos')
        ->assertOk()
        ->assertSee('Le projet Mini-CMS et ses objectifs.')
        ->assertSee('Projet pédagogique')
        ->assertSee('Données provisoires')
        ->assertSee('heni anas')
        ->assertSee('aria-current="page"', false);
});

test('article index renders four cards and marks the articles navigation active', function () {
    $response = $this->get('/posts')
        ->assertOk()
        ->assertSee('À la une')
        ->assertSee('Préparer la suite du projet')
        ->assertSee(route('posts.show', 'bienvenue-sur-mini-cms'))
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
    $this->get('/posts/comprendre-le-routage')
        ->assertOk()
        ->assertSee("Une route associe une URL à une action. Donner un nom à la route permet de générer le lien avec route().")
        ->assertSee("Les paramètres, comme le slug d'un article, sont transmis automatiquement à la méthode du contrôleur.")
        ->assertSee(route('posts.index'))
        ->assertSee('aria-current="page"', false);

    $this->get('/posts/inconnu')->assertNotFound();
});
