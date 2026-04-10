<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('logs in successfully with valid credentials', function () {
    $user = User::factory()->create([
        'email' => 'admin@example.com',
        'password' => Hash::make('password123'),
        'status' => 'active',
        'role' => 'admin',
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'admin@example.com',
        'password' => 'password123',
    ]);

    $response
        ->assertStatus(200)
        ->assertJsonStructure([
            'message',
            'token',
            'user' => ['id', 'name', 'email', 'role', 'status'],
        ]);
});

it('fails login with invalid credentials', function () {
    $user = User::factory()->create([
        'email' => 'admin@example.com',
        'password' => Hash::make('password123'),
        'status' => 'active',
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'admin@example.com',
        'password' => 'wrong-password',
    ]);

    $response
        ->assertStatus(401)
        ->assertJson([
            'message' => 'Invalid credentials',
        ]);
});
