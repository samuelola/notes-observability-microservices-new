<?php

namespace Modules\Notes\Infrastructure\Cache;

use Illuminate\Support\Facades\Cache;
use Modules\Notes\Application\Contracts\CacheInterface;

class LaravelCache implements CacheInterface
{
    public function forget(string $key): void
    {
        Cache::forget($key);
    }

    public function tags(string $key): void
    {
        Cache::tags([$key])->flush();
    }
}
