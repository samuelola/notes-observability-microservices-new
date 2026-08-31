<?php

namespace Modules\Auth\Infrastructure\Messaging;

use Illuminate\Support\Facades\Log;
use Modules\Auth\Application\Contracts\EventPublisherInterface;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMQPublisher implements EventPublisherInterface
{
    public function publish(
        string $eventName,
        array $payload
    ): void {
        $connection = new AMQPStreamConnection(
            config('rabbitmq.host'),
            config('rabbitmq.port'),
            config('rabbitmq.user'),
            config('rabbitmq.password')
        );

        $channel = $connection->channel();

        $exchange = 'app.events';

        $channel->exchange_declare(
            $exchange,
            'topic',
            false,
            true,
            false
        );

        $message = new AMQPMessage(
            json_encode($payload),
            [
                'content_type' => 'application/json',
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
            ]
        );

        $channel->basic_publish(
            $message,
            $exchange,
            $eventName
        );

        Log::info('Event published', [
            'event' => $eventName,
            'payload' => $payload,
        ]);

        $channel->close();
        $connection->close();
    }
}