<?php

namespace Modules\Notes\Application\Queries;

class GetNoteQuery
{
    public function __construct(

        public int $id,
        public int $userId

    ) {}
}
