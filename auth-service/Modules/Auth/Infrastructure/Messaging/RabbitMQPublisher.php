<?php

namespace Modules\Auth\Infrastructure\Messaging;

use Illuminate\Support\Facades\Log;
use Modules\Auth\Application\Contracts\EventPublisherInterface;
use Modules\Auth\Infrastructure\Tracing\TraceManager;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMQPublisher implements EventPublisherInterface
{
    public function __construct(
        private TraceManager $trace,
    ) {}

    public function publish(string $queue, array $payload): void
    {

        $payload['correlation_id'] = $payload['correlation_id']
        ?? request()->header('X-Correlation-ID');

        $payload['trace_id'] = $payload['trace_id']
        ?? request()->header('trace_id');

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

        // $channel->basic_publish($message, '', $queue);

        $this->trace->span(
            'RabbitMQ Publish',
            function () use ($channel, $message, $queue) {

                $channel->basic_publish($message, '', $queue);

            },
            [
                'messaging.system' => 'rabbitmq',
                'messaging.destination' => 'notes-destination',
            ]
        );

        Log::info('Message published successfully');

        $channel->close();
        $connection->close();
    }
}
