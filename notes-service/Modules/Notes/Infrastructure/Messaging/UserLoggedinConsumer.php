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

        $exchange = 'app.events';
        $queue = 'note.userloggedin';

        // Declare exchange
        $channel->exchange_declare(
            $exchange,
            'topic',
            false,
            true,
            false
        );

        // Declare Email Service queue
        $channel->queue_declare(
            $queue,
            false,
            true,
            false,
            false
        );

        // Bind queue to note.created
        $channel->queue_bind(
            $queue,
            $exchange,
            'auth.loggedin'
        );

        $channel->basic_consume(
            $queue,
            '',
            false,
            false, // manual acknowledgement
            false,
            false,
            function ($message) {

                try {

                     $user = json_decode(
                        $message->body,
                        true,
                        512,
                        JSON_THROW_ON_ERROR
                    );

                    Log::info('user.loggedin', [
                    'service' => 'notes',
                    'user_id' => $user['id'],
                    'correlation_id' => $user['correlation_id'] ?? null,
                    // 'note_msg' => $note->toArray(),
                    ]);

                    

                    // Acknowledge message
                    $message->delivery_info['channel']->basic_ack(
                        $message->delivery_info['delivery_tag']
                    );

                } catch (\Throwable $e) {

                    Log::error('Failed processing user.loggedin', [
                        'error' => $e->getMessage(),
                        'message' => $message->body,
                    ]);

                    // You could reject/requeue here
                    $message->delivery_info['channel']->basic_nack(
                        $message->delivery_info['delivery_tag'],
                        false,
                        false
                    );
                }
            }
        );

        while ($channel->is_consuming()) {
            $channel->wait();
        }
    }
}
