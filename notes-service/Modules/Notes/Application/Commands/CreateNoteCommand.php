<?php

namespace Modules\Notes\Application\Commands;

class CreateNoteCommand
{
    // public function __construct(
    //     public string $title,
    //     public string $content,
    //     public int $userId
    // ) {}

    public string $title;

    public string $content;

    public int $userId;

    public function __construct($title, $content, $userId)
    {

        $this->title = $title;
        $this->content = $content;
        $this->userId = $userId;
    }
}
