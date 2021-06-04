<?php

namespace ForkCMS\Core\Installer\Domain\Locale;

use ForkCMS\Core\Domain\Locale\Locale;
use ForkCMS\Core\Installer\Domain\Installer\InstallationData;
use ForkCMS\Core\Installer\Domain\Installer\InstallerConfiguration;
use ForkCMS\Core\Installer\Domain\Installer\InstallerStepConfiguration;
use ForkCMS\Core\Installer\Domain\Installer\InstallerStep;
use Symfony\Component\Validator\Constraints as Assert;

final class LocalesStepConfiguration implements InstallerStepConfiguration
{
    /**
     * The type of locale setup: single or multiple
     */
    public bool $multilingual = false;

    /**
     * Do we use the same locales for the backend or not?
     */
    public bool $sameInterfaceLocale = true;

    /**
     * The locales to install Fork in
     *
     * @var Locale[]
     * @Assert\Count(min=1)
     */
    public array $locales = [];

    /**
     * The backend interface locales to install for Fork
     *
     * @var Locale[]
     * @Assert\Count(min=1)
     */
    public array $interfaceLocales = [];

    /**
     * The default locale for this Fork installation
     * @Assert\NotBlank()
     */
    public ?Locale $defaultLocale = null;

    /**
     * The default locale for the Fork backend
     * @Assert\NotBlank()
     */
    public ?Locale $defaultInterfaceLocale = null;

    private function __construct(
        bool $multilingual,
        array $locales,
        array $interfaceLocales,
        ?Locale $defaultLocale,
        ?Locale $defaultInterfaceLocale,
    ) {
        $this->multilingual = $multilingual;
        $this->setLocales(...$locales);
        $this->setInterfaceLocales(...$interfaceLocales);
        $this->sameInterfaceLocale = $this->locales === $this->interfaceLocales;
        $this->defaultLocale = $defaultLocale;
        $this->defaultInterfaceLocale = $defaultInterfaceLocale;
    }

    public static function fromInstallerConfiguration(InstallerConfiguration $installerConfiguration): static
    {
        if (!$installerConfiguration->hasStep(self::getStep())) {
            return new self(false, [], [], null, null);
        }

        return new self(
            $installerConfiguration->isMultilingual(),
            $installerConfiguration->getLocales(),
            $installerConfiguration->getInterfaceLocales(),
            $installerConfiguration->getDefaultLocale(),
            $installerConfiguration->getDefaultInterfaceLocale(),
        );
    }

    public function normalise(): void
    {
        if (!$this->multilingual) {
            $this->locales = [$this->defaultLocale];
        }

        if ($this->sameInterfaceLocale) {
            $this->interfaceLocales = $this->locales;
        }
    }

    private function setLocales(Locale ...$locales): void
    {
        $this->locales = $locales;
    }

    private function setInterfaceLocales(Locale ...$interfaceLocales): void
    {
        $this->interfaceLocales = $interfaceLocales;
    }

    public static function getStep(): InstallerStep
    {
        return InstallerStep::locales();
    }
}
