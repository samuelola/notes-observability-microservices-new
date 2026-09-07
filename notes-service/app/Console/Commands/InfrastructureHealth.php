<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class InfrastructureHealth extends Command
{
    protected $signature = 'infrastructure:health';

    protected $description = 'Check the health of infrastructure services';

    public function handle(): int
    {
        $this->info('Infrastructure Health');
        $this->newLine();

        $mysql = $this->checkMysql();
        $redis = $this->checkRedis();
        $rabbitmq = $this->checkRabbitMQ();

        $this->newLine();

        if ($mysql && $redis && $rabbitmq) {
            $this->info('All infrastructure services are healthy.');

            return self::SUCCESS;
        }

        $this->error('One or more infrastructure services are unhealthy.');

        return self::FAILURE;
    }

    private function checkMysql(): bool
    {
        try {
            DB::connection()->getPdo();

            $this->line('MySQL      <fg=green>✅ OK</>');

            return true;
        } catch (\Throwable $e) {
            $this->line('MySQL      <fg=red>❌ FAIL</>');

            return false;
        }
    }

    private function checkRedis(): bool
    {
        try {
            Redis::connection()->ping();

            $this->line('Redis      <fg=green>✅ OK</>');

            return true;
        } catch (\Throwable $e) {
            $this->line('Redis      <fg=red>❌ FAIL</>');

            return false;
        }
    }

    private function checkRabbitMQ(): bool
    {
        try {
            $connection = new AMQPStreamConnection(
                config('rabbitmq.host'),
                config('rabbitmq.port'),
                config('rabbitmq.user'),
                config('rabbitmq.password'),
                config('rabbitmq.vhost', '/')
            );

            $connection->close();

            $this->line('RabbitMQ   <fg=green>✅ OK</>');

            return true;
        } catch (\Throwable $e) {
            $this->line('RabbitMQ   <fg=red>❌ FAIL</>');

            return false;
        }
    }
}
