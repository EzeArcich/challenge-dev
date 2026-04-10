<?php

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows an active authenticated user to create an article', function () {
    $user = User::factory()->create([
        'status' => 'active',
        'role' => 'editor',
    ]);

    $category = Category::factory()->create();

    $payload = [
        'title' => 'Mi primer artículo',
        'content' => 'Contenido del artículo de prueba',
        'status' => 'draft',
        'category_ids' => [$category->id],
    ];

    $response = $this
        ->actingAs($user, 'sanctum')
        ->postJson('/api/articles', $payload);

    $response
        ->assertStatus(201)
        ->assertJsonPath('message', 'Article created successfully')
        ->assertJsonPath('data.title', 'Mi primer artículo')
        ->assertJsonPath('data.status', 'draft');

    $this->assertDatabaseHas('articles', [
        'title' => 'Mi primer artículo',
        'status' => 'draft',
        'user_id' => $user->id,
    ]);

    $article = Article::first();

    expect($article->slug)->toBe('mi-primer-articulo');
    expect($article->categories()->count())->toBe(1);
});

it('does not allow an inactive user to create an article', function () {
    $user = User::factory()->create([
        'status' => 'inactive',
        'role' => 'editor',
    ]);

    $category = Category::factory()->create();

    $payload = [
        'title' => 'Artículo bloqueado',
        'content' => 'Este artículo no debería crearse',
        'status' => 'draft',
        'category_ids' => [$category->id],
    ];

    $response = $this
        ->actingAs($user, 'sanctum')
        ->postJson('/api/articles', $payload);

    $response
        ->assertStatus(403)
        ->assertJson([
            'message' => 'Only active users can create articles',
        ]);

    $this->assertDatabaseMissing('articles', [
        'title' => 'Artículo bloqueado',
    ]);
});
