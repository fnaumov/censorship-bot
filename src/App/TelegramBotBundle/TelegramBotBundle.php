<?php

declare(strict_types=1);

namespace App\TelegramBotBundle;

use App\TelegramBotBundle\DependencyInjection\TelegramBotExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class TelegramBotBundle extends AbstractBundle
{
    public function getContainerExtension(): ExtensionInterface
    {
        return new TelegramBotExtension();
    }
}
