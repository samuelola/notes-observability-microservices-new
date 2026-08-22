<?php

namespace Modules\Auth\Infrastructure\Messaging;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Modules\Auth\Application\Contracts\EventPublisherInterface;
use Illuminate\Support\Facades\Log;

class RabbitMQPublisher implements EventPublisherInterface
{

    public function publish(string $queue, array $payload): void
    {
        
        Log::info('Publishing to RabbitMQ', [
        'queue' => $queue,
        'payload' => $payload,
        ]);


        $connection = new AMQPStreamConnection(
            config('rabbitmq.host'),
            config('rabbitmq.port'),
            config('rabbitmq.user'),
            config('rabbitmq.password')
        );

        $channel = $connection->channel();

        $channel->queue_declare($queue, false, true, false, false);

        $message = new AMQPMessage(json_encode($payload));

        $channel->basic_publish($message, '', $queue);

        Log::info('Message published successfully');

        $channel->close();
        $connection->close();
    }
}