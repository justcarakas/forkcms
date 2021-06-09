<?php

namespace ForkCMS\Core\Installer\Domain\Configuration;

use ForkCMS\Modules\Internationalisation\Domain\Locale\Locale;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleInstallerLocator;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleName;
use ForkCMS\Core\Installer\Domain\Authentication\AuthenticationStepConfiguration;
use ForkCMS\Core\Installer\Domain\Database\DatabaseStepConfiguration;
use ForkCMS\Core\Installer\Domain\Locale\LocalesStepConfiguration;
use ForkCMS\Core\Installer\Domain\Module\ModulesStepConfiguration;
use InvalidArgumentException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Yaml\Tag\TaggedValue;
use Symfony\Component\Yaml\Yaml;

final class ConfigurationParser
{
    public function __construct(
        private string $rootDir,
        private ModuleInstallerLocator $moduleInstallerLocator,
        private MessageBusInterface $commandBus
    ) {
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
            'different-debug-email' => $authentication->differentDebugEmail,
            'debug-email' => $authentication->debugEmail,
            'database-hostname' => $withCredentials ? $database->databaseHostname : null,
            'database-port' => $withCredentials ? $database->databasePort : null,
            'database-name' => $withCredentials ? $database->databaseName : null,
            'database-username' => $withCredentials ? $database->databaseUsername : null,
            'database-password' => $withCredentials ? $database->databasePassword : null,
            'multilingual' => $locales->multilingual,
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
            'install-example-data' => $modules->installExampleData,
        ];

        return Yaml::dump($configuration);
    }

    public function loadFromYaml(InstallerConfiguration $installerConfiguration, string $yamlComfiguration): void
    {
        $configuration = Yaml::parse($yamlComfiguration, Yaml::PARSE_CUSTOM_TAGS);
        $moduleNameMap = $this->moduleInstallerLocator->getAllModuleNames();
        $configuration['modules'] = array_map(
            static function (TaggedValue $taggedModule) use ($moduleNameMap): ModuleName {
                if (!array_key_exists($taggedModule->getValue(), $moduleNameMap)) {
                    throw new InvalidArgumentException(
                        'The module "' . $taggedModule->getValue() . '" does not exist.'
                    );
                }

                return $moduleNameMap[$taggedModule->getValue()];
            },
            $configuration['modules'],
        );
        $configuration['locales'] = array_map(
            static fn(TaggedValue $taggedLocale) => Locale::from($taggedLocale->getValue()),
            $configuration['locales'],
        );
        $configuration['default-locale'] = Locale::from($configuration['default-locale']->getValue());
        $configuration['interface-locales'] = array_map(
            static fn(TaggedValue $taggedLocale) => Locale::from($taggedLocale->getValue()),
            $configuration['interface-locales'],
        );
        $configuration['default-interface-locale'] = Locale::from(
            $configuration['default-interface-locale']->getValue()
        );

        $installerConfiguration->withLocaleStep(LocalesStepConfiguration::fromArray($configuration));
        $installerConfiguration->withModulesStep(
            ModulesStepConfiguration::fromArray($configuration),
            $this->moduleInstallerLocator,
            $this->commandBus
        );
        $installerConfiguration->withDatabaseStep(DatabaseStepConfiguration::fromArray($configuration));
        $installerConfiguration->withAuthenticationStep(AuthenticationStepConfiguration::fromArray($configuration));
    }

    public function loadFromFile(InstallerConfiguration $installerConfiguration): void
    {
        if (!$this->configurationFileExists()) {
            return;
        }

        $this->loadFromYaml($installerConfiguration, file_get_contents($this->getFilename()));
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
