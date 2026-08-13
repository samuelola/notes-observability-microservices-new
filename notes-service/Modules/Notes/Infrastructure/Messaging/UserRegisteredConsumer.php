<?php

namespace Modules\Notes\Infrastructure\Messaging;

use Illuminate\Support\Facades\Log;
use Modules\Notes\Infrastructure\Persistence\Models\Note;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class UserRegisteredConsumer
{
    public function consume(): void
    {
        $connection = new AMQPStreamConnection(
            config('rabbitmq.host'),
            config('rabbitmq.port'),
            config('rabbitmq.user'),
            config('rabbitmq.password')
        );

        $channel = $connection->channel();

        $channel->queue_declare('user.registered', false, true, false, false);

        $channel->basic_consume(
            'user.registered',
            '',
            false,
            true,
            false,
            false,
            function ($message) {

                $user = json_decode($message->body, true);

                Log::info('Publishing to RabbitMQ', $user);
                Note::create([
                    'user_id' => $user['id'],
                    'title' => 'Welcome!',
                    'content' => 'Thanks for joining. This is your first note.',
                ]);
            }
        );

        Log::info('RabbitMQ consumer started');

        while ($channel->is_consuming()) {
            $channel->wait();
        }
    }
}
