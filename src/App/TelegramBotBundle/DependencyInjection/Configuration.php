<?php

declare(strict_types=1);

namespace App\TelegramBotBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('telegram_bot');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('censorship')
                    ->children()
                        ->scalarNode('bot_username')->end()
                        ->scalarNode('bot_api_key')->end()
                        ->booleanNode('user_ban_enable')->end()
                        ->integerNode('user_ban_ttl_seconds')->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
