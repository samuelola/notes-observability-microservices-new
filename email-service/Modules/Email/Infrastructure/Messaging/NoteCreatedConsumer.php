<?php

namespace Modules\Email\Infrastructure\Messaging;

use App\Mail\NoteCreatedMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Modules\Email\Infrastructure\Exceptions\BusinessException;
use Modules\Email\Infrastructure\Exceptions\TransientInfrastructureException;
use Modules\Email\Infrastructure\Persistence\Models\EmailUser;
use Modules\Email\Infrastructure\Shared\IdempotencyService;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

class NoteCreatedConsumer
{
    private const EXCHANGE = 'app.events';

    private const QUEUE = 'email.note.created';

    private const ROUTING_KEY = 'note.created';

    private const RETRY_1 = 'email.note.created.retry.1';

    private const RETRY_2 = 'email.note.created.retry.2';

    private const RETRY_3 = 'email.note.created.retry.3';

    private const DLX = 'app.events.dlx';

    private const DLQ = 'email.note.created.dlq';

    private const DLQ_ROUTING_KEY = 'email.note.created.dlq';

    private const RECONNECT_DELAY = 5;

    private const MAX_RETRIES = 3;

    private IdempotencyService $idempotency;

    public function __construct(
        IdempotencyService $idempotency
    ) {
        $this->idempotency = $idempotency;
    }

    public function consume(): void
    {
        while (true) {

            $connection = null;
            $channel = null;

            try {

                /*
                 * =================================================
                 * CONNECT
                 * =================================================
                 */

                $connection = $this->connectToRabbitMQ();

                $channel = $connection->channel();

                /*
                 * =================================================
                 * PUBLISHER CONFIRMS
                 * =================================================
                 */

                $channel->confirm_select();

                /*
                 * =================================================
                 * TOPOLOGY
                 * =================================================
                 */

                $this->declareTopology($channel);

                /*
                 * =================================================
                 * CONSUMER
                 * =================================================
                 */

                $this->startConsumer($channel);

                Log::info('NoteCreatedConsumer started', [
                    'queue' => self::QUEUE,
                    'exchange' => self::EXCHANGE,
                    'routing_key' => self::ROUTING_KEY,
                ]);

                /*
                 * =================================================
                 * CONSUME
                 * =================================================
                 */

                while ($channel->is_consuming()) {
                    $channel->wait();
                }

            } catch (\Throwable $e) {

                /*
                 * RabbitMQ connection/runtime failure.
                 *
                 * This is infrastructure failure, not a
                 * business-processing failure.
                 */

                Log::error(
                    'RabbitMQ runtime failure. Consumer will reconnect.',
                    [
                        'queue' => self::QUEUE,
                        'error' => $e->getMessage(),
                        'exception' => get_class($e),
                    ]
                );

                $this->closeConnection(
                    $channel,
                    $connection
                );

                sleep(self::RECONNECT_DELAY);
            }
        }
    }

    /**
     * Connect to RabbitMQ.
     */
    private function connectToRabbitMQ(): AMQPStreamConnection
    {
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

                return $connection;

            } catch (\Throwable $e) {

                Log::warning(
                    'RabbitMQ connection failed. Retrying in 5 seconds...',
                    [
                        'error' => $e->getMessage(),
                    ]
                );

                sleep(self::RECONNECT_DELAY);
            }
        }
    }

    /**
     * Declare RabbitMQ topology.
     */
    private function declareTopology($channel): void
    {
        /*
         * =================================================
         * MAIN EXCHANGE
         * =================================================
         */

        $channel->exchange_declare(
            self::EXCHANGE,
            'topic',
            false,
            true,
            false
        );

        /*
         * =================================================
         * MAIN QUEUE
         * =================================================
         */

        $channel->queue_declare(
            self::QUEUE,
            false,
            true,
            false,
            false
        );

        $channel->queue_bind(
            self::QUEUE,
            self::EXCHANGE,
            self::ROUTING_KEY
        );

        /*
         * =================================================
         * DLX
         * =================================================
         */

        $channel->exchange_declare(
            self::DLX,
            'topic',
            false,
            true,
            false
        );

        /*
         * =================================================
         * DLQ
         * =================================================
         */

        $channel->queue_declare(
            self::DLQ,
            false,
            true,
            false,
            false
        );

        $channel->queue_bind(
            self::DLQ,
            self::DLX,
            self::DLQ_ROUTING_KEY
        );

        /*
         * =================================================
         * RETRY QUEUES
         * =================================================
         */

        $this->declareRetryQueue(
            $channel,
            self::RETRY_1,
            5000
        );

        $this->declareRetryQueue(
            $channel,
            self::RETRY_2,
            15000
        );

        $this->declareRetryQueue(
            $channel,
            self::RETRY_3,
            30000
        );
    }

    /**
     * Start RabbitMQ consumer.
     */
    private function startConsumer($channel): void
    {
        $channel->basic_consume(
            self::QUEUE,
            '',
            false,
            false,
            false,
            false,
            function (AMQPMessage $message) use ($channel) {

                $this->processMessage(
                    $message,
                    $channel
                );
            }
        );
    }

    /**
     * Process note.created event.
     */
    private function processMessage(
        AMQPMessage $message,
        $channel
    ): void {

        try {

            /*
             * =================================================
             * PARSE MESSAGE
             * =================================================
             */

            $data = json_decode(
                $message->body,
                true,
                512,
                JSON_THROW_ON_ERROR
            );

            if (! is_array($data)) {

                throw new BusinessException(
                    'Invalid RabbitMQ message payload'
                );
            }

            /*
             * =================================================
             * VALIDATE EVENT
             * =================================================
             */

            $eventId = $data['event_id'] ?? null;

            if (! $eventId) {

                throw new BusinessException(
                    'Missing event_id in RabbitMQ message'
                );
            }

            $eventName =
                $data['event']
                ?? self::ROUTING_KEY;

            $userId = $data['user_id'] ?? null;

            if (! $userId) {

                throw new BusinessException(
                    'Missing user_id in note.created event'
                );
            }

            Log::info('note.created received', [
                'service' => 'email',
                'event_id' => $eventId,
                'user_id' => $userId,
            ]);

            /*
             * =================================================
             * IDEMPOTENT PROCESSING
             * =================================================
             */

            $processed = $this->idempotency->process(
                $eventId,
                $eventName,
                function () use ($data) {

                    /*
                     * =================================================
                     * FIND USER
                     * =================================================
                     *
                     * A missing user is a business/application
                     * problem, not a temporary Redis/RabbitMQ
                     * infrastructure failure.
                     */

                    $user = EmailUser::where(
                        'user_id',
                        $data['user_id']
                    )->first();

                    if (! $user) {

                        throw new BusinessException(
                            "Email user not found: {$data['user_id']}"
                        );
                    }

                    /*
                     * =================================================
                     * BUILD EMAIL DATA
                     * =================================================
                     */

                    $emailData = [
                        'name' => $user->name,
                        'note_id' => $data['note_id'] ?? null,
                        'title' => $data['title'] ?? 'Untitled Note',
                        'content' => $data['content'] ?? '',
                    ];

                    /*
                     * =================================================
                     * QUEUE EMAIL
                     * =================================================
                     *
                     * Redis is being used by Laravel's queue here.
                     *
                     * If Redis is temporarily unavailable,
                     * this operation should become a
                     * TransientInfrastructureException.
                     */

                    try {

                        Mail::to($user->email)
                            ->queue(
                                new NoteCreatedMail($emailData)
                            );

                    } catch (\RedisException $e) {

                        Log::warning(
                            'Redis/mail queue infrastructure failure',
                            [

                                'user_id' => $user->user_id,
                                'note_id' => $data['note_id'] ?? null,
                                'error' => $e->getMessage(),
                            ]
                        );

                        throw new TransientInfrastructureException(
                            'Unable to queue email because the queue infrastructure is unavailable.',
                            0,
                            $e
                        );
                    }

                    Log::info('Note email queued', [
                        'user_id' => $user->user_id,
                        'email' => $user->email,
                        'note_id' => $data['note_id'] ?? null,
                    ]);
                }
            );

            /*
             * =================================================
             * DUPLICATE EVENT
             * =================================================
             */

            if (! $processed) {

                Log::warning(
                    'Duplicate note.created event detected. Skipping.',
                    [
                        'event_id' => $eventId,
                        'event_name' => $eventName,

                    ]
                );
            }

            /*
             * =================================================
             * SUCCESS
             * =================================================
             *
             * Either:
             *
             * 1. Event was successfully processed
             * 2. Event was already processed
             *
             * In both cases it is safe to ACK.
             */

            $channel->basic_ack(
                $message->getDeliveryTag()
            );

        } catch (BusinessException $e) {

            /*
             * =================================================
             * BUSINESS FAILURE
             * =================================================
             *
             * Do NOT retry.
             *
             * Example:
             *
             * - invalid event
             * - missing user
             * - invalid data
             *
             * Send directly to DLQ.
             */

            Log::error(
                'Business failure processing note.created',
                [
                    'error' => $e->getMessage(),
                    'message' => $message->body,
                    'exception' => get_class($e),
                ]
            );

            $this->sendToDlq(
                $message,
                $channel,
                $e
            );

        } catch (TransientInfrastructureException $e) {

            /*
             * =================================================
             * TRANSIENT INFRASTRUCTURE FAILURE
             * =================================================
             *
             * Retry.
             *
             * Example:
             *
             * - Redis unavailable
             * - temporary queue infrastructure failure
             */

            $retryCount = $this->getRetryCount(
                $message
            );

            Log::warning(
                'Transient infrastructure failure processing note.created',
                [
                    'error' => $e->getMessage(),
                    'retry_count' => $retryCount,
                ]
            );

            if ($retryCount >= self::MAX_RETRIES) {

                $this->sendToDlq(
                    $message,
                    $channel,
                    $e
                );

                return;
            }

            $nextRetry = $retryCount + 1;

            $this->sendToRetryQueue(
                $message,
                $channel,
                $nextRetry
            );

        } catch (\Throwable $e) {

            /*
             * =================================================
             * UNKNOWN FAILURE
             * =================================================
             *
             * We don't know whether this is safe to retry.
             *
             * For now, treat unknown failures as business
             * failures and send them to DLQ.
             *
             * This prevents accidental infinite retries.
             */

            Log::error(
                'Unknown failure processing note.created',
                [
                    'error' => $e->getMessage(),
                    'exception' => get_class($e),
                    'message' => $message->body,
                ]
            );

            $this->sendToDlq(
                $message,
                $channel,
                $e
            );
        }
    }

    /**
     * Read retry count from message headers.
     */
    private function getRetryCount(
        AMQPMessage $message
    ): int {

        $headers = $message->get(
            'application_headers'
        );

        if (! $headers instanceof AMQPTable) {
            return 0;
        }

        $headers = $headers->getNativeData();

        return (int) (
            $headers['x-retry-count'] ?? 0
        );
    }

    /**
     * Send message to retry queue.
     */
    private function sendToRetryQueue(
        AMQPMessage $message,
        $channel,
        int $retryNumber
    ): void {

        $retryQueue = match ($retryNumber) {

            1 => self::RETRY_1,

            2 => self::RETRY_2,

            3 => self::RETRY_3,

            default => throw new \InvalidArgumentException(
                'Invalid retry number'
            ),
        };

        $headers = $message->get(
            'application_headers'
        );

        $nativeHeaders =
            $headers instanceof AMQPTable
                ? $headers->getNativeData()
                : [];

        $nativeHeaders['x-retry-count'] =
            $retryNumber;

        $retryMessage = new AMQPMessage(
            $message->body,
            [
                'content_type' => 'application/json',

                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,

                'application_headers' => new AMQPTable(
                    $nativeHeaders
                ),
            ]
        );

        /*
         * Publish retry message.
         */

        $channel->basic_publish(
            $retryMessage,
            '',
            $retryQueue
        );

        /*
         * Confirm publish before ACKing original.
         */

        $channel->wait_for_pending_acks_returns();

        /*
         * Only ACK original after confirmed publish.
         */

        $channel->basic_ack(
            $message->getDeliveryTag()
        );

        Log::warning(
            'Message published and confirmed in retry queue',
            [
                'retry_number' => $retryNumber,
                'retry_queue' => $retryQueue,
            ]
        );
    }

    /**
     * Send message to DLQ.
     */
    private function sendToDlq(
        AMQPMessage $message,
        $channel,
        \Throwable $exception
    ): void {

        $headers = $message->get(
            'application_headers'
        );

        $nativeHeaders =
            $headers instanceof AMQPTable
                ? $headers->getNativeData()
                : [];

        $nativeHeaders['x-error'] =
            $exception->getMessage();

        $nativeHeaders['x-error-type'] =
            get_class($exception);

        $dlqMessage = new AMQPMessage(
            $message->body,
            [
                'content_type' => 'application/json',

                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,

                'application_headers' => new AMQPTable(
                    $nativeHeaders
                ),
            ]
        );

        /*
         * Publish to DLQ.
         */

        $channel->basic_publish(
            $dlqMessage,
            self::DLX,
            self::DLQ_ROUTING_KEY
        );

        /*
         * Confirm DLQ publish before ACK.
         */

        $channel->wait_for_pending_acks_returns();

        /*
         * ACK original only after DLQ publish
         * has been confirmed.
         */

        $channel->basic_ack(
            $message->getDeliveryTag()
        );

        Log::error(
            'Message published and confirmed in DLQ',
            [
                'queue' => self::DLQ,
                'error' => $exception->getMessage(),
                'error_type' => get_class($exception),
            ]
        );
    }

    /**
     * Declare retry queue with TTL.
     */
    private function declareRetryQueue(
        $channel,
        string $queue,
        int $ttl
    ): void {

        $arguments = new AMQPTable([
            'x-message-ttl' => $ttl,

            'x-dead-letter-exchange' => self::EXCHANGE,

            'x-dead-letter-routing-key' => self::ROUTING_KEY,
        ]);

        $channel->queue_declare(
            $queue,
            false,
            true,
            false,
            false,
            false,
            $arguments
        );
    }

    /**
     * Safely close RabbitMQ resources.
     */
    private function closeConnection(
        $channel,
        $connection
    ): void {

        try {

            if ($channel && $channel->is_open()) {
                $channel->close();
            }

        } catch (\Throwable $e) {

            Log::debug(
                'RabbitMQ channel cleanup failed',
                [
                    'error' => $e->getMessage(),
                ]
            );
        }

        try {

            if (
                $connection
                && $connection->isConnected()
            ) {
                $connection->close();
            }

        } catch (\Throwable $e) {

            Log::debug(
                'RabbitMQ connection cleanup failed',
                [
                    'error' => $e->getMessage(),
                ]
            );
        }
    }
}
