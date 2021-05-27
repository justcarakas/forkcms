<?php

namespace ForkCMS\Core\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\FileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\DependencyInjection\Extension\Extension;

class CoreExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        // Nothing needs to be loaded here
    }

    public function prepend(ContainerBuilder $container): void
    {
        $this->getLoader($container)->load('doctrine.yaml');

        return;
        $filesystem = new Filesystem();
        foreach ((array) $container->getParameter('installed_modules') as $module) {
            $dir = $container->getParameter('kernel.project_dir') . '/src/Backend/Modules/' . $module . '/Entity';

            if (!$filesystem->exists($dir)) {
                continue;
            }

            /*
             * Find and load entities in the backend folder.
             * We do this by looping all installed modules and looking for an Entity directory.
             * If the Entity map is found, a configuration will be prepended to the configuration.
             * So it's basically like if you would add every single module by hand, but automagically.
             *
             * @TODO Check for YAML/XML files and set the type accordingly
             */
            $container->prependExtensionConfig(
                'doctrine',
                [
                    'orm' => [
                        'mappings' => [
                            $module => [
                                'type' => 'annotation',
                                'is_bundle' => false,
                                'dir' => $dir,
                                'prefix' => 'Backend\\Modules\\' . $module . '\\Entity',
                            ],
                        ],
                    ],
                ]
            );
        }
    }

    private function getLoader(ContainerBuilder $container): YamlFileLoader
    {
        return  new YamlFileLoader($container, new FileLocator(__DIR__ . '/../config'));
    }
}
