<?php

declare(strict_types=1);

namespace ForkCMS\Core\Domain\MessageHandler;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * Used to register handlers automatically in the command bus.
 */
#[AutoconfigureTag('messenger.message_handler', attributes: ['bus' => 'command.bus'])]
interface CommandHandlerInterface
{
}
