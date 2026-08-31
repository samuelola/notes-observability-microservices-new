<?php

namespace Modules\Notes\Application\Contracts;

interface EventPublisherInterface
{
    public function publish(string $event, array $payload): void;
}
