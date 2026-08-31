<?php

namespace Modules\Email\Infrastructure\Messaging;

use App\Mail\NoteCreatedMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Modules\Email\Infrastructure\Persistence\Models\EmailUser;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class NoteCreatedConsumer
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
        $queue = 'email.note.created';

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
            'note.created'
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

                    $data = json_decode(
                        $message->body,
                        true
                    );

                    Log::info('note.created received', [
                        'service' => 'email',
                        'data' => $data,
                    ]);

                    // Find the user from Email Service's local projection
                    $user = EmailUser::where(
                        'user_id',
                        $data['user_id']
                    )->first();

                    if (! $user) {
                        throw new \RuntimeException(
                            "Email user not found: {$data['user_id']}"
                        );
                    }

                    $emailData = [
                        'name' => $user->name,
                        'note_id' => $data['note_id'] ?? null,
                        'title' => $data['title'] ?? 'Untitled Note',
                        'content' => $data['content'] ?? '',
                    ];

                    // Put the email into Laravel's queue
                    Mail::to($user->email)
                        ->queue(
                            new NoteCreatedMail($emailData)
                        );

                    Log::info('Note email queued', [
                        'message' => 'user email confirmed and ready to send email',
                    ]);

                    // Log::info('Note email queued', [
                    //     'user_id' => $user->user_id,
                    //     'email' => $user->email,
                    //     'note_id' => $data['note_id'] ?? null,
                    //     'message' => 'user email confirmed and ready to send email'
                    // ]);

                    // Tell RabbitMQ we successfully processed the event
                    $message->delivery_info['channel']->basic_ack(
                        $message->delivery_info['delivery_tag']
                    );

                } catch (\Throwable $e) {

                    Log::error('Failed processing note.created', [
                        'error' => $e->getMessage(),
                        'message' => $message->body,
                    ]);

                    // Requeue the message
                    $message->delivery_info['channel']->basic_nack(
                        $message->delivery_info['delivery_tag'],
                        false,
                        false
                    );
                }
            }
        );

        Log::info('NoteCreatedConsumer started');

        while ($channel->is_consuming()) {
            $channel->wait();
        }
    }
}
