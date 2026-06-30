<?php

namespace ForkCMS\Modules\Installer\Domain\Configuration;

use ForkCMS\Core\Domain\Kernel\Command\ClearContainerCache;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleInstallerLocator;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleName;
use ForkCMS\Modules\Installer\Domain\Authentication\AuthenticationStepConfiguration;
use ForkCMS\Modules\Installer\Domain\Database\DatabaseStepConfiguration;
use ForkCMS\Modules\Installer\Domain\Installer\InstallerStep;
use ForkCMS\Modules\Installer\Domain\Locale\LocalesStepConfiguration;
use ForkCMS\Modules\Installer\Domain\Module\ModulesStepConfiguration;
use ForkCMS\Modules\Internationalisation\Domain\Locale\Locale;
use InvalidArgumentException;
use LogicException;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\Cache\ItemInterface;

final class InstallerConfiguration
{
    /** @var InstallerStep[] */
    private array $withSteps = [];
    private(set) bool $multilingual;
    private(set) Locale $defaultLocale;
    private(set) Locale $defaultUserLocale;
    /** @var Locale[] */
    private(set) array $locales = [];
    /** @var Locale[] */
    private(set) array $userLocales = [];
    /** @var ModuleName[] */
    private(set) array $modules = [];
    private(set) bool $installExampleData;
    private(set) bool $differentDebugEmail;
    private(set) ?string $debugEmail;
    private(set) string $databaseHostname;
    private(set) string $databaseUsername;
    private(set) string $databasePassword;
    private(set) string $databaseName;
    private(set) int $databasePort;
    private(set) string $adminEmail;
    private(set) string $adminPassword;
    private(set) bool $saveConfiguration;
    private(set) bool $saveConfigurationWithCredentials;

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
        $this->addStep(InstallerStep::REQUIREMENTS);
    }

    public function withLocaleStep(LocalesStepConfiguration $localesStepConfiguration): self
    {
        $localesStepConfiguration->normalise();
        $this->multilingual = $localesStepConfiguration->multilingual;
        $this->defaultLocale = $localesStepConfiguration->defaultLocale
            ?? throw new InvalidArgumentException('A default locale is missing');
        $this->locales = array_map(
            static fn (Locale $locale) => $locale,
            $localesStepConfiguration->locales
        );
        $this->defaultUserLocale = $localesStepConfiguration->defaultUserLocale
            ?? throw new InvalidArgumentException('A default interface locale is missing');
        $this->userLocales = array_map(
            static fn (Locale $locale) => $locale,
            $localesStepConfiguration->userLocales
        );
        $this->addStep($localesStepConfiguration::getStep());

        return $this;
    }

    public function withModulesStep(
        ModulesStepConfiguration $modulesStepConfiguration,
        ModuleInstallerLocator $moduleInstallerLocator,
        MessageBusInterface $commandBus
    ): self {
        $modulesStepConfiguration->normalise($moduleInstallerLocator);

        $this->modules = array_map(
            static fn (ModuleName $moduleName) => $moduleName,
            $modulesStepConfiguration->modules
        );
        $this->installExampleData = $modulesStepConfiguration->installExampleData;

        $this->addStep($modulesStepConfiguration::getStep());
        $commandBus->dispatch(new ClearContainerCache());

        return $this;
    }

    public function withDatabaseStep(DatabaseStepConfiguration $databaseStepConfiguration): self
    {
        if (!$databaseStepConfiguration->canConnectToDatabase()) {
            throw new LogicException(
                'Invalid database credentials for database: ' . $databaseStepConfiguration->databaseName
            );
        }

        $this->databaseHostname = (string) $databaseStepConfiguration->databaseHostname;
        $this->databaseName = (string) $databaseStepConfiguration->databaseName;
        $this->databaseUsername = (string) $databaseStepConfiguration->databaseUsername;
        $this->databasePassword = (string) $databaseStepConfiguration->databasePassword;
        $this->databasePort = $databaseStepConfiguration->databasePort;

        $this->addStep($databaseStepConfiguration::getStep());

        return $this;
    }

    public function withAuthenticationStep(AuthenticationStepConfiguration $authenticationStepConfiguration): self
    {
        $authenticationStepConfiguration->normalise();

        $this->adminEmail = (string) $authenticationStepConfiguration->email;
        $this->adminPassword = (string) $authenticationStepConfiguration->password;
        $this->differentDebugEmail = $authenticationStepConfiguration->differentDebugEmail;
        $this->debugEmail = $authenticationStepConfiguration->debugEmail;
        $this->saveConfiguration = $authenticationStepConfiguration->saveConfiguration;
        $this->saveConfigurationWithCredentials = $authenticationStepConfiguration->saveConfigurationWithCredentials;

        $this->addStep($authenticationStepConfiguration::getStep());

        return $this;
    }

    private static function getCache(): FilesystemAdapter
    {
        return new FilesystemAdapter('forkcms_installer', 3600);
    }

    public static function fromCache(): InstallerConfiguration
    {
        return self::getCache()->get(
            'installer.configuration',
            function (ItemInterface $item) {
                $item->expiresAfter(3600);
                $configuration = new InstallerConfiguration();
                $item->set($configuration);

                return $configuration;
            }
        );
    }

    public static function toCache(InstallerConfiguration $installerConfiguration): void
    {
        $cache = self::getCache();
        $cacheItem = $cache->getItem('installer.configuration');
        $cacheItem->expiresAfter(3600);
        $cacheItem->set($installerConfiguration);
        $cache->save($cacheItem);
    }
}
