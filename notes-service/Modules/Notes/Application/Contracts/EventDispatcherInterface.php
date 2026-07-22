<?php

namespace Modules\Notes\Application\Contracts;

interface EventDispatcherInterface
{
    public function dispatch(object $event): void;
}
