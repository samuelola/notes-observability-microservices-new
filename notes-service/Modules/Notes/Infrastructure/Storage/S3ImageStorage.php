<?php

namespace Modules\Notes\Infrastructure\Storage;

use Illuminate\Support\Facades\Storage;
use Modules\Notes\Application\Contracts\ImageStorageInterface;

class S3ImageStorage implements ImageStorageInterface
{
    public function store($file, $directory)
    {
        return $file->store($directory, 's3', 'public');
    }

    public function delete($path)
    {
        Storage::disk('s3')->delete($path);
    }

    public function url($path)
    {
        return Storage::disk('s3')->url($path);
    }

    public function temporaryUrl(
    string $path,
    int $minutes = 10
    ){
        return Storage::disk('s3')->temporaryUrl(
            $path,
            now()->addMinutes($minutes)
        );
    }
}
