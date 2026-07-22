<?php

namespace Modules\Notes\Infrastructure\Events;

use Modules\Notes\Application\Contracts\EventDispatcherInterface;

class LaravelEventDispatcher implements EventDispatcherInterface
{
    public function dispatch(object $event): void
    {
        event($event);
    }
}
