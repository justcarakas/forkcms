<?php

namespace ForkCMS\Core\Domain\Kernel;

use ForkCMS\Core\DependencyInjection\CoreExtension;
use ForkCMS\Core\Installer\Domain\Configuration\InstallerConfiguration;
use ForkCMS\Modules\Extensions\Domain\Module\InstalledModules;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleName;
use PDO;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
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
        return $this->isInstalled || !str_ends_with($this->environment, 'install');
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

    private function registerModuleExtensions(ContainerBuilder $container): void
    {
        $filesystem = new Filesystem();
        $finder = new Finder();
        $installedModules = new InstalledModules($container->getParameter('fork.is_installed'));

        foreach ($installedModules() as $module) {
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

            $dependencyInjectionNamespace = 'ForkCMS\\Modules\\' . $module . '\\DependencyInjection\\';
            $dependencyInjectionExtension = $dependencyInjectionNamespace . $module . 'Extension';

            if (class_exists($dependencyInjectionExtension)) {
                $container->registerExtension(new $dependencyInjectionExtension());
            }

            $compilerPassDirectory = $moduleDirectory . '/DependencyInjection/CompilerPass/';
            if ($filesystem->exists($compilerPassDirectory)) {
                $compilerPassNamespace = $dependencyInjectionNamespace . 'CompilerPass\\';
                foreach ($finder->in($compilerPassDirectory)->files()->name('*.php') as $compilerPass) {
                    $compilerPassFQCN = $compilerPassNamespace . substr($compilerPass->getFilename(), 0, -4);
                    $container->addCompilerPass(new $compilerPassFQCN());
                }
            }
        }

        // ensure these extensions are implicitly loaded
        $container->getCompilerPassConfig()->setMergePass(
            new MergeExtensionConfigurationPass(array_keys($container->getExtensions()))
        );
    }
}
