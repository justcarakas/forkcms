<?php

namespace ForkCMS\Core\Installer\Domain\Locale;

use ForkCMS\Core\Domain\MessageHandler\CommandHandlerInterface;
use ForkCMS\Core\Installer\Domain\Installer\InstallerConfiguration;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class LocalesStepConfigurationHandler implements CommandHandlerInterface
{
    public function __construct(private SessionInterface $session)
    {
    }

    public function __invoke(LocalesStepConfiguration $localesStepConfiguration)
    {
        InstallerConfiguration::fromSession($this->session)->withLocaleStep($localesStepConfiguration);
    }
}
