<?php

declare(strict_types=1);

namespace App\TelegramBotBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class TelegramBotExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('telegram_bot.censorship.bot_username', $config['censorship']['bot_username']);
        $container->setParameter('telegram_bot.censorship.bot_api_key', $config['censorship']['bot_api_key']);
        $container->setParameter('telegram_bot.censorship.user_ban_enable', $config['censorship']['user_ban_enable']);
        $container->setParameter('telegram_bot.censorship.user_ban_ttl_seconds', $config['censorship']['user_ban_ttl_seconds']);
    }
}
