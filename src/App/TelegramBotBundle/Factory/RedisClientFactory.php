<?php

declare(strict_types=1);

namespace App\TelegramBotBundle\Factory;

use PRedis\Client;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\RedisAdapter;

class RedisClientFactory extends AbstractController
{
    public function __construct(
        private string $dsn,
    ) {}

    public function createRedisClient(): Client
    {
        return RedisAdapter::createConnection(
            $this->dsn
        );
    }
}