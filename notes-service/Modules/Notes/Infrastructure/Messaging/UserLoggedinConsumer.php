<?php

namespace Modules\Notes\Infrastructure\Messaging;

use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class UserLoggedinConsumer
{
    public function consume(): void
    {
        /*
         * Keep trying to connect to RabbitMQ.
         *
         * This protects the consumer when:
         * - RabbitMQ is still starting
         * - RabbitMQ is temporarily unavailable
         * - RabbitMQ is restarted
         */
        while (true) {
            try {
                Log::info('Connecting to RabbitMQ...', [
                    'host' => config('rabbitmq.host'),
                    'port' => config('rabbitmq.port'),
                ]);

                $connection = new AMQPStreamConnection(
                    config('rabbitmq.host'),
                    config('rabbitmq.port'),
                    config('rabbitmq.user'),
                    config('rabbitmq.password')
                );

                Log::info('RabbitMQ connection established');

                break;

            } catch (\Throwable $e) {
                Log::warning(
                    'RabbitMQ connection failed. Retrying in 5 seconds...',
                    [
                        'error' => $e->getMessage(),
                    ]
                );

                sleep(5);
            }
        }

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

        // Declare queue
        $channel->queue_declare(
            $queue,
            false,
            true,
            false,
            false
        );

        // Bind queue to auth.loggedin
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

                    /*
                     * Reject the message without requeueing.
                     *
                     * This preserves your existing behavior.
                     */
                    $message->delivery_info['channel']->basic_nack(
                        $message->delivery_info['delivery_tag'],
                        false,
                        false
                    );
                }
            }
        );

        Log::info('UserLoggedinConsumer started', [
            'queue' => $queue,
            'exchange' => $exchange,
            'routing_key' => 'auth.loggedin',
        ]);

        while ($channel->is_consuming()) {
            $channel->wait();
        }
    }
}
