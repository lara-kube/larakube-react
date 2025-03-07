<?php

declare(strict_types=1);

use App\Actions\Organization\CreateOrganizationAction;
use App\Models\Organization;
use App\Models\User;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

test('two users can have a different organizations with same name', function (): void {

    $user = User::factory()->createQuietly([
        'name' => 'Test User',
    ]);

    (app(CreateOrganizationAction::class))->handle($user);

    $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();

    expect(Organization::query()->count())
        ->toBe(2);

});

test('new users have a default organization', function (): void {

    $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $this->assertDatabaseHas('organizations', [
        'name' => 'Test User',
    ]);

});

test('registration screen can be rendered', function (): void {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function (): void {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route(
        name: 'dashboard',
        parameters: ['organization' => auth()->user()->organizations()->first()->slug],
        absolute: false)
    );
});
