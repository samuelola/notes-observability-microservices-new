<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Notes\Application\Contracts\EventDispatcherInterface;
use Modules\Notes\Domain\Contracts\AuthClientInterface;
use Modules\Notes\Domain\Events\NoteCreated;

uses(RefreshDatabase::class);

it('creates a note successfully', function () {

    /*
       Now your feature test never touches HTTP at all. with the code AuthClientInterface
    */

    $authClient = Mockery::mock(AuthClientInterface::class);

    $authClient
        ->shouldReceive('userFromToken')
        ->once()
        ->andReturn([
            'id' => 10,
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

    $this->app->instance(
        AuthClientInterface::class,
        $authClient
    );

    $events = Mockery::mock(EventDispatcherInterface::class);
    // mock require expectation
    $events->shouldReceive('dispatch')
        ->once()
        ->with(Mockery::type(NoteCreated::class));

    $this->app->instance(
        EventDispatcherInterface::class,
        $events
    );

    $response = $this->postJson('/api/v1/notes', [
        'title' => 'Shopping List',
        'content' => 'Buy milk',
    ], [
        // however your service authenticates
        'Authorization' => 'Bearer fake-token',
    ]);

    $response
        ->assertCreated()
        ->assertJson([
            'status' => 'success',
        ]);

    $this->assertDatabaseHas('notes', [
        'title' => 'Shopping List',
        'content' => 'Buy milk',
    ]);

});
