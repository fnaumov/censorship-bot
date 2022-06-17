<?php

declare(strict_types=1);

namespace App\TelegramBotBundle\Factory;

use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Telegram;
use Longman\TelegramBot\TelegramLog;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TelegramBotFactory extends AbstractController
{
    public function __construct(
        private string $botUsername,
        private string $botApiKey,
    ) {}

    /**
     * @throws TelegramException
     */
    public function createTelegramBot(LoggerInterface $logger): Telegram
    {
        try {
            $telegram = new Telegram($this->botApiKey, $this->botUsername);
            $telegram->useGetUpdatesWithoutDatabase();

            TelegramLog::initialize($logger, $logger);
        } catch (TelegramException $e) {
            $logger->error($e->getMessage());

            throw $e;
        }

        return $telegram;
    }
}