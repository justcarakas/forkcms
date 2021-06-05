<?php

namespace ForkCMS\Core\Installer\Domain\Installer;

use ForkCMS\Core\Installer\Domain\Configuration\InstallerConfiguration;

interface InstallerStepConfiguration
{
    public static function getStep(): InstallerStep;
    public static function fromInstallerConfiguration(InstallerConfiguration $installerConfiguration): static;
    public static function fromArray(array $configuration): static;
}
