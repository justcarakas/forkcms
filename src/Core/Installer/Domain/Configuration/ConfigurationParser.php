<?php

namespace ForkCMS\Core\Installer\Domain\Configuration;

use ForkCMS\Core\Domain\Locale\Locale;
use ForkCMS\Core\Domain\Module\ModuleName;
use ForkCMS\Core\Installer\Domain\Authentication\AuthenticationStepConfiguration;
use ForkCMS\Core\Installer\Domain\Database\DatabaseStepConfiguration;
use ForkCMS\Core\Installer\Domain\Locale\LocalesStepConfiguration;
use ForkCMS\Core\Installer\Domain\Module\ModulesStepConfiguration;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Tag\TaggedValue;
use Symfony\Component\Yaml\Yaml;

final class ConfigurationParser
{
    public function __construct(private string $rootDir)
    {
    }

    public function toFile(InstallerConfiguration $installerConfiguration): void
    {
        file_put_contents($this->getFilename(), $this->toYaml($installerConfiguration));
    }

    public function toYaml(InstallerConfiguration $installerConfiguration): string
    {
        $withCredentials = $installerConfiguration->shouldSaveConfigurationWithCredentials();
        $authentication = AuthenticationStepConfiguration::fromInstallerConfiguration($installerConfiguration);
        $database = DatabaseStepConfiguration::fromInstallerConfiguration($installerConfiguration);
        $locales = LocalesStepConfiguration::fromInstallerConfiguration($installerConfiguration);
        $modules = ModulesStepConfiguration::fromInstallerConfiguration($installerConfiguration);
        $configuration = [
            'admin-email' => $withCredentials ? $authentication->email : null,
            'admin-password' => $withCredentials ? $authentication->password : null,
            'different-debug-email' => (int) $authentication->differentDebugEmail,
            'debug-email' => $authentication->debugEmail,
            'database-hostname' => $withCredentials ? $database->databaseHostname : null,
            'database-port' => $withCredentials ? $database->databasePort : null,
            'database-name' => $withCredentials ? $database->databaseName : null,
            'database-username' => $withCredentials ? $database->databaseUsername : null,
            'database-password' => $withCredentials ? $database->databasePassword : null,
            'multilingual' => (int) $locales->multilingual,
            'locales' => array_map(
                static fn(Locale $locale) => new TaggedValue('fork-cms_locale', $locale->value),
                $locales->locales
            ),
            'default-locale' => new TaggedValue('fork-cms_locale', $locales->defaultLocale->value),
            'interface-locales' => array_map(
                static fn(Locale $locale) => new TaggedValue('fork-cms_locale', $locale->value),
                $locales->interfaceLocales
            ),
            'default-interface-locale' => new TaggedValue('fork-cms_locale', $locales->defaultInterfaceLocale->value),
            'modules' => array_map(
                static fn(ModuleName $moduleName) => new TaggedValue('fork-cms_module', $moduleName->getName()),
                $modules->modules
            ),
            'install-example-data' => (int) $modules->installExampleData,
        ];

        return Yaml::dump($configuration);
    }

    public function fromYaml(string $yamlComfiguration): InstallerConfiguration
    {

    }

    public function fromFile(): InstallerConfiguration
    {
        return $this->fromYaml(file_get_contents($this->getFilename()));
    }

    public function configurationFileExists(): bool
    {
        return file_exists($this->getFilename());
    }

    private function getFilename(): string
    {
        return $this->rootDir . '/fork-cms-installation-configuration.yaml';
    }
}
