<?php

namespace ForkCMS\Core\Installer\Domain\Installer;

interface InstallerStepConfiguration
{
    public static function getStep(): InstallerStep;

    public static function fromInstallerConfiguration(InstallerConfiguration $installerConfiguration): static;
}
