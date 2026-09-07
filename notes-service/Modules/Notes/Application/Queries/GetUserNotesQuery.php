<?php

namespace Modules\Notes\Application\Queries;

class GetUserNotesQuery
{
    public function __construct(

        public int $userId,
        // public int $page = 1
    ) {}
}
