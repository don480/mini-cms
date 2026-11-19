<?php

use App\Models\Tag;

beforeEach(function () {
    $this->withoutVite();
});

function tagAdminData(array $overrides = []): array
{
    return array_merge([
        'name' => 'Sécurité',
        'slug' => 'securite',
    ], $overrides);
}

it('creates a tag and flashes a message', function () {
    $this->followingRedirects()
        ->post(route('admin.tags.store'), tagAdminData())
        ->assertOk()
        ->assertSee('Tag créé.');

    $this->assertDatabaseHas('tags', ['slug' => 'securite']);
});

it('rejects an empty tag with French messages', function () {
    $this->post(route('admin.tags.store'), ['name' => '', 'slug' => ''])
        ->assertSessionHasErrors([
            'name' => 'Le champ nom est obligatoire.',
            'slug' => 'Le champ slug est obligatoire.',
        ]);
});

it('rejects a slug already used by another tag', function () {
    Tag::factory()->create(['name' => 'Laravel', 'slug' => 'laravel']);

    $this->post(route('admin.tags.store'), tagAdminData(['slug' => 'laravel']))
        ->assertSessionHasErrors(['slug' => 'Ce slug est déjà utilisé par un autre tag.']);
});

it('blocks tag writes when the site is read-only', function () {
    config(['site.read_only' => true]);

    $this->post(route('admin.tags.store'), tagAdminData())
        ->assertStatus(503)
        ->assertSee('Le site est en lecture seule.');
});

it('still shows the tag list when the site is read-only', function () {
    config(['site.read_only' => true]);

    $this->get(route('admin.tags.index'))->assertOk();
});
