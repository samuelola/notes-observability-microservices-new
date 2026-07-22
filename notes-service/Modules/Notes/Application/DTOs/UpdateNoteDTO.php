<?php

namespace Modules\Notes\Application\DTOs;

class UpdateNoteDTO
{
    // public function __construct(
    //     public readonly string $title,
    //     public readonly string $content,
    //     public readonly int $userId,
    // ) {}

    public readonly string $title;

    public readonly string $content;

    public readonly int $userId;

    public readonly int $noteId;

    public function __construct($title, $content, $userId, $noteId)
    {

        $this->title = $title;
        $this->content = $content;
        $this->userId = $userId;
        $this->noteId = $noteId;
    }

    public static function fromArray(array $data, int $userId, int $noteId): self
    {
        return new self(
            title: $data['title'],
            content: $data['content'],
            userId: $userId,
            noteId : $noteId
        );
    }
}
