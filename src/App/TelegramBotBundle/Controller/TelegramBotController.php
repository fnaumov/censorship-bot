<?php

declare(strict_types=1);

namespace App\TelegramBotBundle\Controller;

use App\TelegramBotBundle\Factory\RedisClientFactory;
use DateTimeImmutable;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;
use Predis\Client;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Wkhooy\ObsceneCensorRus;

class TelegramBotController extends AbstractController
{
    #[Route('/censorship-bot/update', methods: ['POST'])]
    public function update(
        RedisClientFactory $redisFactory,
        Telegram $telegram,
        LoggerInterface $censorshipBotLogger
    ): Response {
        $redis = $redisFactory->createRedisClient();

        $telegram->setUpdateFilter(function (Update $update, Telegram $telegram, &$reason) use ($redis) {
            $message = $this->getMessage($update);

            if (!$message) {
                return false;
            }

            $userId = $message->getFrom()->getId();

            if ($redis->get($this->getUserBanRedisKey($userId)) !== null) {
                $reason = sprintf('User `%u` is blocked', $userId);

                $this->deleteMessage($message);

                return false;
            }

            if (!ObsceneCensorRus::isAllowed($message->getText())) {
                $reason = sprintf('Message is obscene - `%s`', $message->getText());

                $this->messageObsceneHandle($message, $redis, $userId);

                return false;
            }

            return true;
        });

        try {
            $telegram->handle();
        } catch (TelegramException $e) {
            $censorshipBotLogger->error('Exception:' . $e->getMessage());
        }

        return new Response();
    }

    private function messageObsceneHandle(Message $message, Client $redis, int $userId): void
    {
        if ($this->isUserBanEnable()) {
            $this->addUserBanToRedis($redis, $userId);
            $this->banChatMember($message);
        }

        $this->deleteMessage($message);
    }

    private function addUserBanToRedis(Client $redis, int $userId): void
    {
        $cacheKey = $this->getUserBanRedisKey($userId);

        $redis->set($cacheKey, '1');
        $redis->expire($cacheKey, $this->getUserBanTtlSeconds());
    }

    private function deleteMessage(Message $message): void
    {
        Request::deleteMessage([
            'chat_id' => $message->getChat()->getId(),
            'message_id' => $message->getMessageId(),
        ]);
    }

    private function banChatMember(Message $message): void
    {
        $modifyString = sprintf('+ %u seconds', $this->getUserBanTtlSeconds());

        Request::banChatMember([
            'chat_id' => $message->getChat()->getId(),
            'user_id' => $message->getFrom()->getId(),
            'until_date' => (new DateTimeImmutable($modifyString))->getTimestamp(),
        ]);
    }

    private function getMessage(Update $update): ?Message
    {
        return $update->getMessage() ?? $update->getEditedMessage();
    }

    private function isUserBanEnable(): bool
    {
        return (bool) $this->getParameter('telegram_bot.censorship.user_ban_enable');
    }

    private function getUserBanTtlSeconds(): int
    {
        return (int) $this->getParameter('telegram_bot.censorship.user_ban_ttl_seconds');
    }

    private function getUserBanRedisKey(int $userId): string
    {
        return sprintf('ban:user_id:%u', $userId);
    }
}
