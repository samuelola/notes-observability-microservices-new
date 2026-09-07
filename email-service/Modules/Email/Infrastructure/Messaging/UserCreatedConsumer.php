<?php

namespace Modules\Email\Infrastructure\Messaging;

use Illuminate\Support\Facades\Log;
use Modules\Email\Infrastructure\Exceptions\BusinessException;
use Modules\Email\Infrastructure\Exceptions\TransientInfrastructureException;
use Modules\Email\Infrastructure\Persistence\Models\EmailUser;
use Modules\Email\Infrastructure\Shared\IdempotencyService;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

class UserCreatedConsumer
{
    private const EXCHANGE = 'app.events';

    private const QUEUE = 'email.user.created';

    private const ROUTING_KEY = 'auth.loggedin';

    private const RETRY_1 = 'email.user.created.retry.1';

    private const RETRY_2 = 'email.user.created.retry.2';

    private const RETRY_3 = 'email.user.created.retry.3';

    private const DLX = 'app.events.dlx';

    private const DLQ = 'email.user.created.dlq';

    private const DLQ_ROUTING_KEY = 'email.user.created.dlq';

    private const RECONNECT_DELAY = 5;

    private IdempotencyService $idempotency;

    public function __construct(
        IdempotencyService $idempotency
    ) {
        $this->idempotency = $idempotency;
    }

    /**
     * Start the consumer.
     *
     * Handles:
     *
     * - RabbitMQ unavailable during startup
     * - RabbitMQ runtime connection failure
     * - Consumer reconnection
     * - Retry queues
     * - Dead-letter queue
     * - Publisher confirms
     * - Idempotency
     * - Business vs infrastructure failures
     */
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

                /*
                 * =================================================
                 * CREATE CHANNEL
                 * =================================================
                 */

                $channel = $connection->channel();

                /*
                 * =================================================
                 * ENABLE PUBLISHER CONFIRMS
                 * =================================================
                 */

                $channel->confirm_select();

                /*
                 * =================================================
                 * DECLARE TOPOLOGY
                 * =================================================
                 */

                $this->declareTopology($channel);

                /*
                 * =================================================
                 * START CONSUMER
                 * =================================================
                 */

                $this->startConsumer($channel);

                Log::info('UserCreatedConsumer started', [
                    'queue' => self::QUEUE,
                    'exchange' => self::EXCHANGE,
                    'routing_key' => self::ROUTING_KEY,
                ]);

                /*
                 * =================================================
                 * CONSUME
                 * =================================================
                 *
                 * Runtime RabbitMQ failures escape from here
                 * and are handled by the outer catch block.
                 */

                while ($channel->is_consuming()) {
                    $channel->wait();
                }

            } catch (\Throwable $e) {

                Log::error(
                    'RabbitMQ connection lost. Consumer will reconnect.',
                    [
                        'queue' => self::QUEUE,
                        'error' => $e->getMessage(),
                        'exception' => get_class($e),
                    ]
                );

                /*
                 * =================================================
                 * CLEANUP
                 * =================================================
                 */

                $this->closeConnection(
                    $channel,
                    $connection
                );

                Log::warning(
                    'Reconnecting to RabbitMQ in '
                    .self::RECONNECT_DELAY
                    .' seconds...'
                );

                sleep(self::RECONNECT_DELAY);
            }
        }
    }

    /**
     * Connect to RabbitMQ.
     *
     * Keeps retrying until RabbitMQ becomes available.
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
     * Declare all RabbitMQ topology.
     */
    private function declareTopology($channel): void
    {
        /*
         * =====================================================
         * MAIN EXCHANGE
         * =====================================================
         */

        $channel->exchange_declare(
            self::EXCHANGE,
            'topic',
            false,
            true,
            false
        );

        /*
         * =====================================================
         * MAIN QUEUE
         * =====================================================
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
         * =====================================================
         * DEAD LETTER EXCHANGE
         * =====================================================
         */

        $channel->exchange_declare(
            self::DLX,
            'topic',
            false,
            true,
            false
        );

        /*
         * =====================================================
         * DEAD LETTER QUEUE
         * =====================================================
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
         * =====================================================
         * RETRY QUEUES
         * =====================================================
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
     * Start consuming messages.
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
            if ($connection && $connection->isConnected()) {
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

    /**
     * Create a retry queue with a TTL.
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
     * Process an incoming message.
     *
     * Business failures and infrastructure failures
     * are handled differently.
     */
    private function processMessage(
        AMQPMessage $message,
        $channel
    ): void {
        try {
            /*
             * =====================================================
             * DECODE MESSAGE
             * =====================================================
             */

            try {
                $data = json_decode(
                    $message->body,
                    true,
                    512,
                    JSON_THROW_ON_ERROR
                );
            } catch (\JsonException $e) {
                throw new BusinessException(
                    'Invalid JSON message payload: '
                    .$e->getMessage(),
                    0,
                    $e
                );
            }

            if (! is_array($data)) {
                throw new BusinessException(
                    'Invalid RabbitMQ message payload'
                );
            }

            /*
             * =====================================================
             * VALIDATE EVENT
             * =====================================================
             */

            $eventId = $data['event_id'] ?? null;

            if (! $eventId) {
                throw new BusinessException(
                    'Missing event_id in RabbitMQ message'
                );
            }

            $eventName = $data['event'] ?? self::ROUTING_KEY;

            if (! isset($data['id'])) {
                throw new BusinessException(
                    'Missing user id in RabbitMQ message'
                );
            }

            if (! isset($data['name'])) {
                throw new BusinessException(
                    'Missing user name in RabbitMQ message'
                );
            }

            if (! isset($data['email'])) {
                throw new BusinessException(
                    'Missing user email in RabbitMQ message'
                );
            }

            Log::info('auth.loggedin received', [
                'event_id' => $eventId,
                'data' => $data,
            ]);

            /*
             * =====================================================
             * IDEMPOTENT PROCESSING
             * =====================================================
             *
             * Database failures are infrastructure failures.
             * They must not be confused with business failures.
             */

            try {
                $processed = $this->idempotency->process(
                    $eventId,
                    $eventName,
                    function () use ($data) {

                        /*
                         * =================================================
                         * BUSINESS LOGIC
                         * =================================================
                         */

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
                    }
                );

            } catch (\Throwable $e) {

                /*
                 * The database/idempotency operation failed.
                 *
                 * We treat this as transient infrastructure
                 * failure because the database may recover.
                 */

                throw new TransientInfrastructureException(
                    'Idempotency/database operation failed: '
                    .$e->getMessage(),
                    0,
                    $e
                );
            }

            /*
             * =====================================================
             * DUPLICATE
             * =====================================================
             */

            if (! $processed) {
                Log::warning(
                    'Duplicate event detected. Skipping processing.',
                    [
                        'event_id' => $eventId,
                        'event_name' => $eventName,
                    ]
                );
            }

            /*
             * =====================================================
             * SUCCESS
             * =====================================================
             *
             * If the event was successfully processed OR was
             * already processed, it is safe to ACK.
             */

            $channel->basic_ack(
                $message->getDeliveryTag()
            );

        } catch (BusinessException $e) {

            /*
             * =====================================================
             * BUSINESS FAILURE
             * =====================================================
             *
             * Example:
             *
             * - Invalid JSON
             * - Missing event_id
             * - Missing required user data
             *
             * Retrying will not fix bad data.
             *
             * Send directly to DLQ.
             */

            Log::error(
                'Business failure processing email.user.created',
                [
                    'error' => $e->getMessage(),
                    'message' => $message->body,
                ]
            );

            $this->sendToDlq(
                $message,
                $channel,
                $e
            );

        } catch (TransientInfrastructureException $e) {

            /*
             * =====================================================
             * TRANSIENT INFRASTRUCTURE FAILURE
             * =====================================================
             *
             * Example:
             *
             * - Database temporarily unavailable
             * - Redis temporarily unavailable
             * - Temporary infrastructure problem
             *
             * Retry.
             */

            $retryCount = $this->getRetryCount($message);

            Log::warning(
                'Transient infrastructure failure processing email.user.created',
                [
                    'error' => $e->getMessage(),
                    'retry_count' => $retryCount,
                    'message' => $message->body,
                ]
            );

            if ($retryCount >= 3) {
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
             * =====================================================
             * UNKNOWN FAILURE
             * =====================================================
             *
             * Do not silently discard an unknown failure.
             *
             * Treat it as transient so that temporary failures
             * have a chance to recover.
             */

            $retryCount = $this->getRetryCount($message);

            Log::error(
                'Unexpected failure processing email.user.created',
                [
                    'error' => $e->getMessage(),
                    'retry_count' => $retryCount,
                    'exception' => get_class($e),
                    'message' => $message->body,
                ]
            );

            if ($retryCount >= 3) {
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
        }
    }

    /**
     * Read retry count from message headers.
     */
    private function getRetryCount(
        AMQPMessage $message
    ): int {
        $headers = $message->get('application_headers');

        if (! $headers instanceof AMQPTable) {
            return 0;
        }

        $headers = $headers->getNativeData();

        return (int) ($headers['x-retry-count'] ?? 0);
    }

    /**
     * Send a message to the appropriate retry queue.
     *
     * The original message is ACKed ONLY after RabbitMQ
     * confirms the retry message.
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

        /*
         * =================================================
         * PRESERVE HEADERS
         * =================================================
         */

        $headers = $message->get('application_headers');

        $nativeHeaders = $headers instanceof AMQPTable
            ? $headers->getNativeData()
            : [];

        $nativeHeaders['x-retry-count'] = $retryNumber;

        /*
         * =================================================
         * CREATE RETRY MESSAGE
         * =================================================
         */

        $retryMessage = new AMQPMessage(
            $message->body,
            [
                'content_type' => 'application/json',

                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,

                'application_headers' => new AMQPTable($nativeHeaders),
            ]
        );

        /*
         * =================================================
         * PUBLISH
         * =================================================
         */

        $channel->basic_publish(
            $retryMessage,
            '',
            $retryQueue
        );

        /*
         * =================================================
         * WAIT FOR BROKER CONFIRMATION
         * =================================================
         */

        $channel->wait_for_pending_acks_returns();

        /*
         * =================================================
         * ACK ORIGINAL
         * =================================================
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
     * Send permanently failed message to DLQ.
     *
     * The original message is ACKed ONLY after RabbitMQ
     * confirms the DLQ publish.
     */
    private function sendToDlq(
        AMQPMessage $message,
        $channel,
        \Throwable $exception
    ): void {
        $headers = $message->get('application_headers');

        $nativeHeaders = $headers instanceof AMQPTable
            ? $headers->getNativeData()
            : [];

        $nativeHeaders['x-retry-count'] = 3;

        $nativeHeaders['x-error'] =
            $exception->getMessage();

        $nativeHeaders['x-exception'] =
            get_class($exception);

        /*
         * =================================================
         * CREATE DLQ MESSAGE
         * =================================================
         */

        $dlqMessage = new AMQPMessage(
            $message->body,
            [
                'content_type' => 'application/json',

                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,

                'application_headers' => new AMQPTable($nativeHeaders),
            ]
        );

        /*
         * =================================================
         * PUBLISH TO DLQ
         * =================================================
         */

        $channel->basic_publish(
            $dlqMessage,
            self::DLX,
            self::DLQ_ROUTING_KEY
        );

        /*
         * =================================================
         * WAIT FOR BROKER CONFIRMATION
         * =================================================
         */

        $channel->wait_for_pending_acks_returns();

        /*
         * =================================================
         * ACK ORIGINAL
         * =================================================
         */

        $channel->basic_ack(
            $message->getDeliveryTag()
        );

        Log::error(
            'Message published and confirmed in DLQ',
            [
                'queue' => self::DLQ,
                'retry_count' => 3,
                'error' => $exception->getMessage(),
                'exception' => get_class($exception),
            ]
        );
    }
}
