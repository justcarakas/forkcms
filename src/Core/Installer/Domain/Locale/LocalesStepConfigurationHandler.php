<?php

namespace ForkCMS\Core\Installer\Domain\Locale;

use ForkCMS\Core\Domain\MessageHandler\CommandHandlerInterface;
use ForkCMS\Core\Installer\Domain\Configuration\InstallerConfiguration;

final class LocalesStepConfigurationHandler implements CommandHandlerInterface
{
    public function __invoke(LocalesStepConfiguration $localesStepConfiguration): void
    {
        InstallerConfiguration::toCache(
            InstallerConfiguration::fromCache()->withLocaleStep($localesStepConfiguration)
        );
    }
}
