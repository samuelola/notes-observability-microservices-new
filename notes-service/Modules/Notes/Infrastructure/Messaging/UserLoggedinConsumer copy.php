<?php

namespace Modules\Notes\Infrastructure\Messaging;

use Illuminate\Support\Facades\Log;
use Modules\Notes\Infrastructure\Persistence\Models\Note;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class UserLoggedinConsumer
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

        $channel->queue_declare('user.loggedin', false, true, false, false);

        $channel->basic_consume(
            'user.loggedin',
            '',
            false,
            false, // auto_ack = false
            false,
            false,
            function ($message) {

                $user = json_decode($message->body, true);

                $note = Note::create([
                    'user_id' => $user['id'],
                    'title' => 'Welcome!',
                    'content' => 'Login is successful',
                ]);

                Log::info('user.loggedin', [
                    'service' => 'notes',
                    'user_id' => $user['id'],
                    'correlation_id' => $user['correlation_id'],
                    'trace_id' => $user['trace_id'],
                    // 'note_msg' => $note->toArray(),
                ]);

                $message->ack();

            }
        );

        Log::info('RabbitMQ consumer logged, Thanks for Logging');

        while ($channel->is_consuming()) {
            $channel->wait();
        }
    }
}
