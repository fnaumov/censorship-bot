<?php

declare(strict_types=1);

namespace App\TelegramBotBundle\Controller;

use App\TelegramBotBundle\Factory\RedisClientFactory;
use DateTimeImmutable;
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
    public function update(RedisClientFactory $redisFactory, Telegram $telegram, LoggerInterface $logger): Response
    {
        $redis = $redisFactory->createRedisClient();

        try {
            $telegram->setUpdateFilter(function (Update $update, Telegram $telegram, &$reason) use ($redis, $logger) {
                $userId = $update->getMessage()->getFrom()->getId();

                if ($redis->get($this->getUserBanRedisKey($userId)) !== null) {
                    $reason = sprintf('User `%u` is blocked', $userId);

                    return false;
                }

                $message = $update->getMessage()->getText();

                if (!ObsceneCensorRus::isAllowed($message)) {
                    $reason = sprintf('Message is obscene - `%s`', $message);

                    $this->addUserBanToRedis($redis, $userId);
                    $this->deleteMessage($update);
                    $this->banChatMember($update);

                    return false;
                }

                return true;
            });

            $telegram->handle();
        } catch (TelegramException $e) {
            $logger->error('Exception:' . $e->getMessage());
        }

        return new Response();
    }

    private function addUserBanToRedis(Client $redis, int $userId): void
    {
        $cacheKey = $this->getUserBanRedisKey($userId);

        $redis->set($cacheKey, '1');
        $redis->expire($cacheKey, $this->getUserBanTtlSeconds());
    }

    private function deleteMessage(Update $update): void
    {
        Request::deleteMessage([
            'chat_id' => $update->getMessage()->getChat()->getId(),
            'message_id' => $update->getMessage()->getMessageId(),
        ]);
    }

    private function banChatMember(Update $update): void
    {
        $modifyString = sprintf('+ %u seconds', $this->getUserBanTtlSeconds());

        Request::banChatMember([
            'chat_id' => $update->getMessage()->getChat()->getId(),
            'user_id' => $update->getMessage()->getFrom()->getId(),
            'until_date' => (new DateTimeImmutable($modifyString))->getTimestamp(),
        ]);
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