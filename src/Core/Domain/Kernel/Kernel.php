<?php

namespace ForkCMS\Core\Domain\Kernel;

use ForkCMS\Core\DependencyInjection\CoreExtension;
use ForkCMS\Core\Installer\Domain\Configuration\InstallerConfiguration;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleName;
use PDO;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\DependencyInjection\MergeExtensionConfigurationPass;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class Kernel extends BaseKernel
{
    private const ROOT_DIR = __DIR__ . '/../../../../';

    use MicroKernelTrait;

    private bool $isInstalled;

    public function __construct(string $environment, bool $debug)
    {
        $this->isInstalled = file_exists(self::ROOT_DIR . '.env.local');

        if (!$this->isInstalled) {
            $environment = str_contains($environment, 'test') ? 'test_install' : 'install';
        }

        parent::__construct($environment, $debug);
    }

    private function isInstalled(): bool
    {
        return $this->isInstalled || !str_ends_with($this->environment, 'installer');
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        if ($this->isInstalled()) {
            $this->configureLiveContainer($container);

            return;
        }

        $this->configureInstallerContainer($container);
    }

    protected function buildContainer(): ContainerBuilder
    {
        $container = parent::buildContainer();

        $container->registerExtension(new CoreExtension());

        $this->registerModuleExtensions($container);

        return $container;
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        if ($this->isInstalled()) {
            $this->configureLiveRoutes($routes);

            return;
        }

        $this->configureInstallerRoutes($routes);
    }

    private function configureLiveContainer(ContainerConfigurator $container): void
    {
        $container->import(self::ROOT_DIR . 'config/{packages}/*.yaml');
        $container->import(self::ROOT_DIR . 'config/{packages}/' . $this->environment . '/*.yaml');

        $container->import(self::ROOT_DIR . 'config/services.yaml');
        $container->import(self::ROOT_DIR . 'config/{services}_' . $this->environment . '.yaml');
    }

    private function configureInstallerContainer(ContainerConfigurator $container): void
    {
        $container->import(self::ROOT_DIR . 'config/{packages}/install/*.yaml');
        if ($this->environment === 'test_install') {
            $container->import(self::ROOT_DIR . 'config/{packages}/test_install/*.yaml');
        }

        $container->import(self::ROOT_DIR . 'config/{services}_install.yaml');
        if ($this->environment === 'test_install') {
            $container->import(self::ROOT_DIR . 'config/{services}_test_install.yaml');
        }
    }

    private function configureLiveRoutes(RoutingConfigurator $routes): void
    {
        $routes->import(self::ROOT_DIR . 'config/{routes}/' . $this->environment . '/*.yaml');
        $routes->import(self::ROOT_DIR . 'config/{routes}/*.yaml');

        if (is_file(self::ROOT_DIR . 'config/routes.yaml')) {
            $routes->import(self::ROOT_DIR . 'config/routes.yaml');
        }
    }

    private function configureInstallerRoutes(RoutingConfigurator $routes): void
    {
        $routes->import(self::ROOT_DIR . 'config/{routes}/install/*.yaml');
    }

    public function getContainerClass(): string
    {
        return parent::getContainerClass();
    }

    private function registerModuleExtensions(ContainerBuilder $container)
    {
        $filesystem = new Filesystem();
        $modules = $this->getModulesForDependencyInjection($container);

        foreach ($modules as $module) {
            $moduleDirectory = $container->getParameter('kernel.project_dir') . '/src/Modules/' . $module;

            if (!$filesystem->exists($moduleDirectory)) {
                continue;
            }

            $domainDirectory = $moduleDirectory . '/Domain';
            if ($filesystem->exists($domainDirectory)) {
                $container->prependExtensionConfig(
                    'doctrine',
                    [
                        'orm' => [
                            'mappings' => [
                                $module->getName() => [
                                    'type' => 'annotation',
                                    'is_bundle' => false,
                                    'dir' => $domainDirectory,
                                    'prefix' => 'ForkCMS\\Modules\\' . $module . '\\Domain',
                                ],
                            ],
                        ],
                    ]
                );
            }

            $dependencyInjectionExtension = 'ForkCMS\\Modules\\' . $module . '\\DependencyInjection\\' . $module . 'Extension';

            if (class_exists($dependencyInjectionExtension)) {
                $container->registerExtension(new $dependencyInjectionExtension());
            }
        }

        // ensure these extensions are implicitly loaded
        $container->getCompilerPassConfig()->setMergePass(
            new MergeExtensionConfigurationPass(array_keys($container->getExtensions()))
        );
    }

    /** @return ModuleName[] */
    private function getModulesForDependencyInjection(ContainerBuilder $container): array
    {
        if (!$container->getParameter('fork.is_installed')) {
            return InstallerConfiguration::fromSession(new Session())?->getModules() ?? [];
        }

        $connection = new PDO(
            sprintf(
                '%1$s:host=%2$s;port=%3$s;dbname=%4$s',
                $_ENV['FORK_DATABASE_DRIVER'],
                $_ENV['FORK_DATABASE_HOST'],
                $_ENV['FORK_DATABASE_PORT'],
                $_ENV['FORK_DATABASE_NAME'],
            ), $_ENV['FORK_DATABASE_USER'], $_ENV['FORK_DATABASE_PASSWORD']
        );

        $modulesQuery = $connection->query('SELECT name from Modules');
        if (!$modulesQuery->execute()) {
            throw new RuntimeException('Cannot get installed modules from database');
        }

        return array_map(
            static fn(string $moduleName): ModuleName => ModuleName::fromString($moduleName),
            $modulesQuery->fetchAll(PDO::FETCH_COLUMN, 0)
        );
    }
}
