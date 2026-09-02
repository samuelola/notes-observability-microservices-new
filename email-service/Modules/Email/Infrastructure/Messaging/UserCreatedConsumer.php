<?php

namespace Modules\Email\Infrastructure\Messaging;

use Illuminate\Support\Facades\Log;
use Modules\Email\Infrastructure\Persistence\Models\EmailUser;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class UserCreatedConsumer
{
    public function consume(): void
    {
        /*
         * Keep trying to connect to RabbitMQ.
         *
         * This protects the consumer from:
         * - RabbitMQ starting slowly
         * - RabbitMQ being temporarily unavailable
         * - RabbitMQ being restarted
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
                Log::warning('RabbitMQ connection failed. Retrying in 5 seconds...', [
                    'error' => $e->getMessage(),
                ]);

                sleep(5);
            }
        }

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

                    if (! is_array($data)) {
                        throw new \RuntimeException(
                            'Invalid RabbitMQ message payload'
                        );
                    }

                    Log::info('auth.loggedin received', [
                        'data' => $data,
                    ]);

                    // Create or update the user projection
                    $emailUser = EmailUser::updateOrCreate(
                        [
                            'user_id' => $data['id'],
                        ],
                        [
                            'name' => $data['name'],
                            'email' => $data['email'],
                        ]
                    );

                    Log::info(
                        'UserCreatedConsumer user id confirmed',
                        [
                            'user_id' => $emailUser->user_id,
                        ]
                    );

                    // Tell RabbitMQ processing succeeded
                    $message->delivery_info['channel']->basic_ack(
                        $message->delivery_info['delivery_tag']
                    );

                } catch (\Throwable $e) {

                    Log::error(
                        'Failed processing email.user.created',
                        [
                            'error' => $e->getMessage(),
                            'message' => $message->body,
                        ]
                    );

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

        Log::info('UserCreatedConsumer started', [
            'queue' => $queue,
            'exchange' => $exchange,
            'routing_key' => 'auth.loggedin',
        ]);

        while ($channel->is_consuming()) {
            $channel->wait();
        }
    }
}
