<?php

namespace Modules\Notes\Application\DTOs;

class CreateNoteDTO
{
    // public function __construct(
    //     public readonly string $title,
    //     public readonly string $content,
    //     public readonly int $userId,
    // ) {}

    public readonly string $title;

    public readonly string $content;

    public readonly int $userId;

    public $image;

    public function __construct($title, $content, $userId, $image)
    {

        $this->title = $title;
        $this->content = $content;
        $this->userId = $userId;
        $this->image = $image;
    }

    public static function fromArray(array $data, int $userId, $image): self
    {
        return new self(
            title: $data['title'],
            content: $data['content'],
            userId: $userId,
            image: $image
        );
    }
}
