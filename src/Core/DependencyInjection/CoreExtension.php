<?php

namespace ForkCMS\Core\DependencyInjection;

use ForkCMS\Core\Installer\Domain\Configuration\InstallerConfiguration;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleName;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\HttpFoundation\Session\Session;

class CoreExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container): void
    {
    }

    public function prepend(ContainerBuilder $container): void
    {

        $this->getLoader($container)->load('doctrine.yaml');


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
    }

    private function getLoader(ContainerBuilder $container): YamlFileLoader
    {
        return new YamlFileLoader($container, new FileLocator(__DIR__ . '/../config'));
    }

    /** @return ModuleName[] */
    private function getModulesForDependencyInjection(ContainerBuilder $container): array
    {
        if (!$container->getParameter('fork.is_installed')) {
            return InstallerConfiguration::fromSession(new Session())?->getModules() ?? [];
        }

        throw new \RuntimeException('not implemented yet');
    }
}
