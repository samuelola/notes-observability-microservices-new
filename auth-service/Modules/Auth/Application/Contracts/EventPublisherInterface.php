<?php

namespace Modules\Auth\Application\Contracts;

interface EventPublisherInterface
{
    public function publish(string $event, array $payload): void;
}
