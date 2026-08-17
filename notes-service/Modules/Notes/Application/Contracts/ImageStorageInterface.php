<?php

namespace Modules\Notes\Application\Contracts;

interface ImageStorageInterface
{
    public function store($file, $directory);

    public function delete($path);

    public function url($path);

    public function temporaryUrl(
        string $path,
        int $minutes = 10
    );
}
