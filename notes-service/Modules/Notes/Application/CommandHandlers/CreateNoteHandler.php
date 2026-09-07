<?php

namespace Modules\Notes\Application\CommandHandlers;

use Illuminate\Support\Str;
use Modules\Notes\Application\Commands\CreateNoteCommand;
use Modules\Notes\Application\Contracts\CacheInterface;
use Modules\Notes\Application\Contracts\EventDispatcherInterface;
use Modules\Notes\Application\Contracts\EventPublisherInterface;
use Modules\Notes\Application\Contracts\ImageStorageInterface;
use Modules\Notes\Domain\Contracts\AuthClientInterface;
use Modules\Notes\Domain\Events\NoteCreated;
use Modules\Notes\Domain\Repositories\NoteRepositoryInterface;
use Modules\Notes\Infrastructure\Queue\ProcessNoteAnalytics;

class CreateNoteHandler
{
    public function __construct(
        private NoteRepositoryInterface $repo,
        private AuthClientInterface $authClient,
        private CacheInterface $cache,
        private EventDispatcherInterface $events,
        private ImageStorageInterface $imageStorage,
        private EventPublisherInterface $publisher,

    ) {}

    public function handle(CreateNoteCommand $command)
    {

        $imagePath = null;

        if ($command->image) {

            $imagePath = $this->imageStorage->store(
                $command->image,
                "notes/{$command->userId}"
            );

        }

        $note = $this->repo->create([
            'title' => $command->title,
            'content' => $command->content,
            'user_id' => $command->userId,
            'image_path' => $imagePath,
        ]);

        $this->cache->tags(
            "notes:user:{$command->userId}"
        );

        // this event uses listener : Internal domain event

        $this->events->dispatch(
            new NoteCreated($note->id)
        );

        // Integration event → RabbitMQ
        $this->publisher->publish(
            'note.created',
            [
                'event_id' => (string) Str::uuid(),
                'event' => 'note.created',
                'note_id' => $note->id,
                'user_id' => $note->user_id,
                'title' => $note->title,
                'content' => $note->content,
                'created_at' => $note->created_at,
                'correlation_id' => $command->correlation_id,
            ]
        );

        // ProcessNoteAnalytics::dispatch($note->id);
        return $note;
    }
}
