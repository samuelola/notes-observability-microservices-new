<?php

namespace Modules\Notes\Application\Commands;

class CreateNoteCommand
{
    public string $title;

    public string $content;

    public int $userId;

    public $image;

    public function __construct($title, $content, $userId, $image)
    {

        $this->title = $title;
        $this->content = $content;
        $this->userId = $userId;
        $this->image = $image;
    }
}
