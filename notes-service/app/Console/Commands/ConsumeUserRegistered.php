<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Modules\Notes\Infrastructure\Messaging\UserRegisteredConsumer;

#[Signature('rabbitmq:consume-users')]
#[Description('Consume user.registered events')]

class ConsumeUserRegistered extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(UserRegisteredConsumer $consumer)
    {
        $this->info('Waiting for messages...');
        $consumer->consume();
    }
}
