<?php

namespace ForkCMS\Core\Installer\Domain\Installer;

use ForkCMS\Core\Domain\Locale\Locale;
use ForkCMS\Core\Installer\Domain\Locale\LocalesStepConfiguration;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class InstallerConfiguration
{
    /** @var InstallerStep[] */
    private array $withSteps = [];
    private bool $multilingual;
    private Locale $defaultLocale;
    private Locale $defaultInterfaceLocale;
    private array $locales;
    private array $interfaceLocales;

    public static function fromSession(SessionInterface $session): self
    {
        if (!$session->has('installer_configuration')) {
            $session->set('installer_configuration', new self());
        }

        return $session->get('installer_configuration');
    }

    public function isValidForStep(InstallerStep $installerStep): bool
    {
        while ($installerStep->hasPrevious()) {
            $installerStep = $installerStep->previous();
            if (!$this->hasStep($installerStep)) {
                return false;
            }
        }

        return true;
    }

    public function hasStep(InstallerStep $installerStep): bool
    {
        return array_key_exists($installerStep->value, $this->withSteps);
    }

    private function addStep(InstallerStep $installerStep): void
    {
        $this->withSteps[$installerStep->value] = $installerStep;
    }

    public function withLocaleStep(LocalesStepConfiguration $localesStepConfiguration): void
    {
        $this->multilingual = $localesStepConfiguration->multilingual;
        $this->defaultLocale = $localesStepConfiguration->defaultLocale
                               ?? throw new InvalidArgumentException('A default locale is missing');
        $this->locales = array_map(
            static fn(Locale $locale) => $locale,
            $localesStepConfiguration->locales
        );
        $this->defaultInterfaceLocale = $localesStepConfiguration->defaultInterfaceLocale
                                        ?? throw new InvalidArgumentException('A default interface locale is missing');
        $this->interfaceLocales = array_map(
            static fn(Locale $locale) => $locale,
            $localesStepConfiguration->interfaceLocales
        );
        $this->addStep($localesStepConfiguration::getStep());
    }

    public function isMultilingual(): bool
    {
        return $this->multilingual;
    }

    public function getDefaultLocale(): Locale
    {
        return $this->defaultLocale;
    }

    public function getDefaultInterfaceLocale(): Locale
    {
        return $this->defaultInterfaceLocale;
    }

    /** @return Locale[] */
    public function getLocales(): array
    {
        return $this->locales;
    }

    /** @return Locale[] */
    public function getInterfaceLocales(): array
    {
        return $this->interfaceLocales;
    }
}
