<?php

namespace Modules\Email\Infrastructure\Shared;

use Illuminate\Support\Facades\DB;
use Modules\Email\Infrastructure\Persistence\Models\ProcessedEvent;

class IdempotencyService
{
    /**
     * Check whether an event has already been processed.
     */
    public function hasBeenProcessed(string $eventId): bool
    {
        return ProcessedEvent::where(
            'event_id',
            $eventId
        )->exists();
    }

    /**
     * Mark an event as successfully processed.
     */
    public function markAsProcessed(
        string $eventId,
        string $eventName
    ): void {
        ProcessedEvent::create([
            'event_id' => $eventId,
            'event_name' => $eventName,
            'processed_at' => now(),
        ]);
    }

    /**
     * Execute business logic and record the event
     * as processed in the same database transaction.
     */
    public function process(
        string $eventId,
        string $eventName,
        callable $callback
    ): bool {
        return DB::transaction(function () use (
            $eventId,
            $eventName,
            $callback
        ) {

            /*
             * Event was already successfully processed.
             */
            if ($this->hasBeenProcessed($eventId)) {
                return false;
            }

            /*
             * Execute the actual business operation.
             */
            $callback();

            /*
             * Only mark the event after the business operation
             * succeeds.
             */
            $this->markAsProcessed(
                $eventId,
                $eventName
            );

            return true;
        });
    }
}
