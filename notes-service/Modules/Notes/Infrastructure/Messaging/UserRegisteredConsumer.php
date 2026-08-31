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

        $exchange = 'app.events';
        $queue = 'note.userregister';

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
            'auth.registered'
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

                    
                    $user = json_decode($message->body, true);

                    Log::info('Registration is successful', $user);

                    

                    // Acknowledge message
                    $message->delivery_info['channel']->basic_ack(
                        $message->delivery_info['delivery_tag']
                    );

                } catch (\Throwable $e) {

                    Log::error('Failed processing note.created', [
                        'error' => $e->getMessage(),
                        'message' => $message->body
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
