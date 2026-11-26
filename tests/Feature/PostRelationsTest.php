<?php

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;

beforeEach(function () {
    $this->withoutVite();
    $this->actingAs(User::factory()->admin()->create());
});

it('lists posts with their category, author and tags without lazy loading', function () {
    $category = Category::factory()->create(['name' => 'Tutoriels']);
    $tag = Tag::factory()->create(['name' => 'Astuce']);
    $author = User::factory()->create(['name' => 'Amira Ben Salah']);

    $posts = Post::factory(3)->published()->create([
        'category_id' => $category->id,
        'user_id' => $author->id,
    ]);

    $posts->each(fn (Post $post) => $post->tags()->attach($tag));

    $this->get(route('posts.index'))
        ->assertOk()
        ->assertSee('Tutoriels')
        ->assertSee('Amira Ben Salah')
        ->assertSee('Astuce');
});

it('keeps posts when their category is deleted', function () {
    $category = Category::factory()->create();
    $post = Post::factory()->create(['category_id' => $category->id]);

    $category->delete();

    $this->assertDatabaseHas('posts', ['id' => $post->id]);
    expect($post->fresh()->category_id)->toBeNull();
});

it('replaces a post tags with sync', function () {
    $post = Post::factory()->create();
    $tags = Tag::factory(3)->create();

    $post->tags()->attach([$tags[0]->id, $tags[1]->id]);
    $post->tags()->sync([$tags[1]->id, $tags[2]->id]);

    expect($post->tags()->count())->toBe(2);
    $this->assertDatabaseMissing('post_tag', ['post_id' => $post->id, 'tag_id' => $tags[0]->id]);
    $this->assertDatabaseHas('post_tag', ['post_id' => $post->id, 'tag_id' => $tags[2]->id]);
});

it('removes pivot rows when a post is deleted but preserves tags', function () {
    $post = Post::factory()->create();
    $tag = Tag::factory()->create();
    $post->tags()->attach($tag);

    $this->delete(route('posts.destroy', $post));

    $this->assertDatabaseMissing('post_tag', ['post_id' => $post->id]);
    $this->assertDatabaseHas('tags', ['id' => $tag->id]);
});

it('shows up to three other published posts in the same category', function () {
    $category = Category::factory()->create();
    $post = Post::factory()->published()->create([
        'category_id' => $category->id,
        'title' => 'Article principal',
    ]);

    foreach (range(1, 4) as $day) {
        Post::factory()->published()->create([
            'category_id' => $category->id,
            'title' => "Voisin publié {$day}",
            'published_at' => now()->subDays($day),
        ]);
    }

    Post::factory()->create([
        'category_id' => $category->id,
        'title' => 'Voisin brouillon',
    ]);
    Post::factory()->published()->create(['title' => 'Autre catégorie']);

    $response = $this->get(route('posts.show', $post))
        ->assertOk()
        ->assertSee('Dans la même catégorie')
        ->assertSee('Voisin publié 1')
        ->assertSee('Voisin publié 2')
        ->assertSee('Voisin publié 3')
        ->assertDontSee('Voisin publié 4')
        ->assertDontSee('Voisin brouillon')
        ->assertDontSee('Autre catégorie');

    $content = $response->getContent();
    $sectionStart = strpos($content, '<section class="mt-12" aria-labelledby="related-title">');
    $sectionEnd = strpos($content, '</section>', $sectionStart);
    $section = substr($content, $sectionStart, $sectionEnd - $sectionStart);

    expect($section)->not->toContain('Article principal');
});

it('hides the related section when there are no other published posts in the category', function () {
    $post = Post::factory()->published()->create();

    $this->get(route('posts.show', $post))
        ->assertOk()
        ->assertDontSee('Dans la même catégorie');

    $category = Category::factory()->create();
    $categorizedPost = Post::factory()->published()->create(['category_id' => $category->id]);

    $this->get(route('posts.show', $categorizedPost))
        ->assertOk()
        ->assertDontSee('Dans la même catégorie');
});
