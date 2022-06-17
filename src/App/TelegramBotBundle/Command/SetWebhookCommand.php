<?php

declare(strict_types=1);

namespace App\TelegramBotBundle\Command;

use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Telegram;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'telegram-bot:censorship:set-webhook')]
class SetWebhookCommand extends Command
{
    private const ALLOWED_TYPES = [
        Update::TYPE_MESSAGE,
        Update::TYPE_EDITED_MESSAGE,
        Update::TYPE_CHANNEL_POST,
        Update::TYPE_EDITED_CHANNEL_POST,
    ];

    public function __construct(
        private Telegram $telegram,
        private LoggerInterface $logger,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $url = $input->getArgument('url');

            $result = $this->telegram->setWebhook($url, [
                'allowed_updates' => self::ALLOWED_TYPES,
            ]);

            if ($result->isOk()) {
                $output->writeln(sprintf('<info>Webhook url `%s` is set successfully</info>', $url));
            }
        } catch (TelegramException $e) {
            $this->logger->error($e->getMessage());
            $output->writeln(sprintf('<error>Failed: %s</error>', $e->getMessage()));

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('url', InputArgument::REQUIRED, 'Url webhook')
        ;
    }
}
