<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Modules\Auth\Application\Contracts\EventPublisherInterface;
use Modules\Auth\Infrastructure\Persistence\Models\User;

uses(RefreshDatabase::class);

it('logs in successfully with valid credentials', function () {

    $publisher = Mockery::mock(EventPublisherInterface::class);
    $publisher
        ->shouldReceive('publish')
        ->once()
        ->with(
            'user.loggedin',
            Mockery::type('array')
        );
    $this->app->instance(
        EventPublisherInterface::class,
        $publisher
    );
    $user = User::create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => Hash::make('password123'),
    ]);
    $response = $this->postJson('/api/v1/login', [
        'email' => 'john@example.com',
        'password' => 'password123',
    ]);
    $response
        ->assertOk()
        ->assertJson([
            'status' => 'success',
            'message' => 'Login successful',
        ])
        ->assertJsonPath('user.email', 'john@example.com');
    expect($response->json('token'))->not->toBeEmpty();
});
