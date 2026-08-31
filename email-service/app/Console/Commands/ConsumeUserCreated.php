<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Email\Infrastructure\Messaging\UserCreatedConsumer;

class ConsumeUserCreated extends Command
{
    protected $signature = 'rabbitmq:consume-user-created';

    protected $description = 'Consume user.created events from RabbitMQ';

    public function handle(UserCreatedConsumer $consumer): int
    {
        $consumer->consume();

        return self::SUCCESS;
    }
}