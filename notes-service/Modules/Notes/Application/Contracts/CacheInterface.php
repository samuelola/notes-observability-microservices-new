<?php

namespace Modules\Notes\Application\Contracts;

interface CacheInterface
{
    public function forget(string $key): void;

    public function tags(string $key): void;
}
