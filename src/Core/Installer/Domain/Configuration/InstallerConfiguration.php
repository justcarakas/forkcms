<?php

namespace ForkCMS\Core\Installer\Domain\Configuration;

use ForkCMS\Modules\Internationalisation\Domain\Locale\Locale;
use ForkCMS\Core\Domain\Module\ModuleInstallerLocator;
use ForkCMS\Core\Domain\Module\ModuleName;
use ForkCMS\Core\Installer\Domain\Authentication\AuthenticationStepConfiguration;
use ForkCMS\Core\Installer\Domain\Database\DatabaseStepConfiguration;
use ForkCMS\Core\Installer\Domain\Installer\InstallerStep;
use ForkCMS\Core\Installer\Domain\Locale\LocalesStepConfiguration;
use ForkCMS\Core\Installer\Domain\Module\ModulesStepConfiguration;
use InvalidArgumentException;
use LogicException;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class InstallerConfiguration
{
    /** @var InstallerStep[] */
    private array $withSteps = [];
    private bool $multilingual;
    private Locale $defaultLocale;
    private Locale $defaultInterfaceLocale;
    /** @var Locale[] */
    private array $locales;
    /** @var Locale[] */
    private array $interfaceLocales;
    /** @var ModuleName[] */
    private array $modules = [];
    private bool $installExampleData;
    private bool $differentDebugEmail;
    private ?string $debugEmail;
    private string $databaseHostname;
    private string $databaseUsername;
    private string $databasePassword;
    private string $databaseName;
    private int $databasePort;
    private string $adminEmail;
    private string $adminPassword;
    private bool $saveConfiguration;
    private bool $saveConfigurationWithCredentials;

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

    public function withRequirementsStep(): void
    {
        $this->addStep(InstallerStep::requirements());
    }

    public function withLocaleStep(LocalesStepConfiguration $localesStepConfiguration): void
    {
        $localesStepConfiguration->normalise();
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

    public function withModulesStep(
        ModulesStepConfiguration $modulesStepConfiguration,
        ModuleInstallerLocator $moduleInstallerLocator
    ): void {
        $modulesStepConfiguration->normalise($moduleInstallerLocator);

        $this->modules = array_map(
            static fn(ModuleName $moduleName) => $moduleName,
            $modulesStepConfiguration->modules
        );
        $this->installExampleData = $modulesStepConfiguration->installExampleData;

        $this->addStep($modulesStepConfiguration::getStep());
    }

    /** @return ModuleName[] */
    public function getModules(): array
    {
        return $this->modules;
    }

    public function shouldInstallExampleData(): bool
    {
        return $this->installExampleData;
    }

    public function withDatabaseStep(DatabaseStepConfiguration $databaseStepConfiguration): void
    {
        if (!$databaseStepConfiguration->canConnectToDatabase()) {
            throw new LogicException('Invalid database credentials');
        }

        $this->databaseHostname = (string) $databaseStepConfiguration->databaseHostname;
        $this->databaseName = (string) $databaseStepConfiguration->databaseName;
        $this->databaseUsername = (string) $databaseStepConfiguration->databaseUsername;
        $this->databasePassword = (string) $databaseStepConfiguration->databasePassword;
        $this->databasePort = $databaseStepConfiguration->databasePort;

        $this->addStep($databaseStepConfiguration::getStep());
    }

    public function getDatabaseHostname(): string
    {
        return $this->databaseHostname;
    }

    public function getDatabaseUsername(): string
    {
        return $this->databaseUsername;
    }

    public function getDatabasePassword(): string
    {
        return $this->databasePassword;
    }

    public function getDatabaseName(): string
    {
        return $this->databaseName;
    }

    public function getDatabasePort(): int
    {
        return $this->databasePort;
    }

    public function withAuthenticationStep(AuthenticationStepConfiguration $authenticationStepConfiguration): void
    {
        $authenticationStepConfiguration->normalise();

        $this->adminEmail = (string) $authenticationStepConfiguration->email;
        $this->adminPassword = (string) $authenticationStepConfiguration->password;
        $this->differentDebugEmail = $authenticationStepConfiguration->differentDebugEmail;
        $this->debugEmail = $authenticationStepConfiguration->debugEmail;
        $this->saveConfiguration = $authenticationStepConfiguration->saveConfiguration;
        $this->saveConfigurationWithCredentials = $authenticationStepConfiguration->saveConfigurationWithCredentials;

        $this->addStep($authenticationStepConfiguration::getStep());
    }

    public function getAdminEmail(): string
    {
        return $this->adminEmail;
    }

    public function getAdminPassword(): string
    {
        return $this->adminPassword;
    }

    public function hasDifferentDebugEmail(): bool
    {
        return $this->differentDebugEmail;
    }

    public function getDebugEmail(): ?string
    {
        return $this->debugEmail;
    }

    public function shouldSaveConfiguration(): bool
    {
        return $this->saveConfiguration;
    }

    public function shouldSaveConfigurationWithCredentials(): bool
    {
        return $this->saveConfigurationWithCredentials;
    }
}
