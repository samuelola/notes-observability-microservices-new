<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Modules\Notes\Infrastructure\Messaging\UserLoggedinConsumer;

#[Signature('rabbitmq:consume-user-logged-in')]
#[Description('Consume user.Loggedin events')]

class ConsumeUserLoggedIn extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(UserLoggedinConsumer $consumer)
    {
        $this->info('Waiting for messages... from Loggedin user');
        $consumer->consume();
    }
}
