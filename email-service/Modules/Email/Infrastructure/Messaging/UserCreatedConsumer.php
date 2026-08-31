<?php

namespace Modules\Email\Infrastructure\Messaging;

use Illuminate\Support\Facades\Log;
use Modules\Email\Infrastructure\Persistence\Models\EmailUser;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class UserCreatedConsumer
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
        $queue = 'email.user.created';

        // Create the exchange
        $channel->exchange_declare(
            $exchange,
            'topic',
            false,
            true,
            false
        );

        // Create Email Service queue
        $channel->queue_declare(
            $queue,
            false,
            true,
            false,
            false
        );

        // Listen specifically for auth.loggedin
        $channel->queue_bind(
            $queue,
            $exchange,
            'auth.loggedin'
        );

        $channel->basic_consume(
            $queue,
            '',
            false,
            false, // manual ACK
            false,
            false,
            function ($message) {

                try {

                    // Convert RabbitMQ JSON → PHP array
                    $data = json_decode(
                        $message->body,
                        true
                    );

                    Log::info('auth.loggedin received', [
                        'data' => $data,
                    ]);

                    // THIS IS THE STEP YOU ASKED ABOUT
                    $emailUserId = EmailUser::updateOrCreate(
                        [
                            // Search for existing user
                            'user_id' => $data['id'],
                        ],
                        [
                            // Create or update these fields
                            'name' => $data['name'],
                            'email' => $data['email'],
                        ]
                    );

                    Log::info('UserCreatedConsumer user id confirmed', [
                        'user_id' => $emailUserId->user_id,
                    ]);

                    // Tell RabbitMQ processing succeeded
                    $message->delivery_info['channel']->basic_ack(
                        $message->delivery_info['delivery_tag']
                    );

                } catch (\Throwable $e) {

                    Log::error('Failed processing email.user.created', [
                        'error' => $e->getMessage(),
                        'message' => $message->body,
                    ]);

                    // Message processing failed
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
