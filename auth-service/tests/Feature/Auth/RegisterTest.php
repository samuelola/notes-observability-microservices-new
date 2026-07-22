<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Auth\Application\Contracts\EventPublisherInterface;

uses(RefreshDatabase::class);

it('registers a new user successfully', function () {

    $publisher = Mockery::mock(EventPublisherInterface::class);
    $publisher
        ->shouldReceive('publish')
        ->once()
        ->with(
            'user.registered',
            Mockery::type('array')
        );
    $this->app->instance(
        EventPublisherInterface::class,
        $publisher
    );
    $response = $this->postJson('/api/v1/register', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        // 'password_confirmation' => 'password123',
    ]);
    $response
        ->assertCreated()
        ->assertJson([
            'status' => 'success',
        ]);
    $this->assertDatabaseHas('users', [
        'email' => 'john@example.com',
    ]);

});
