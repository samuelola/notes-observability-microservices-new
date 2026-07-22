<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('registers a new user successfully', function () {

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
