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
            false,
            false,
            false,
            function ($message) {

                $this->trace->span(
                    'RabbitMQ Consume User Register',
                    function () use ($message) {

                        $user = json_decode($message->body, true);

                        Log::info('user.registered', [
                            'service' => 'notes',
                            'user_id' => $user['id'],
                            'correlation_id' => $user['correlation_id'],
                            'trace_id' => $user['trace_id'],
                        ]);
                        Note::create([
                            'user_id' => $user['id'],
                            'title' => 'Welcome!',
                            'content' => 'Thanks for joining. This is your first note.',
                        ]);

                        $message->ack();

                    },
                    [
                        'messaging.system' => 'rabbitmq',
                        'messaging.destination' => 'user.registered',
                    ]
                );
            }
        );

        Log::info('RabbitMQ consumer started');

        while ($channel->is_consuming()) {
            $channel->wait();
        }
    }
}
