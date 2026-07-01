<?php

declare(strict_types=1);

namespace ForkCMS\Core\Domain\DependencyInjection;

use ReflectionClass;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Finder\Finder;

/**
 * Base class to load the config for your module. Prepends Fork CMS specific defaults and tagged services.
 */
abstract class ForkModuleExtension extends Extension implements PrependExtensionInterface
{
    #[\Override]
    public function load(array $configs, ContainerBuilder $container): void
    {
    }

    #[\Override]
    public function prepend(ContainerBuilder $container): void
    {
        $this->registerDBALTypes($container);
    }

    final protected function getLoader(ContainerBuilder $container): YamlFileLoader
    {
        $reflector = new ReflectionClass(static::class);

        return new ForkYamlFileLoader(
            $container,
            new FileLocator(dirname($reflector->getFileName()) . '/../config')
        );
    }

    private function registerDBALTypes(ContainerBuilder $container): void
    {
        $reflector = new ReflectionClass(static::class);
        $domainDir = realpath(dirname($reflector->getFileName(), 2) . '/Domain');

        if ($domainDir === false || !is_dir($domainDir)) {
            return;
        }

        $extensionNamespace = $reflector->getNamespaceName();
        $baseNamespace = substr($extensionNamespace, 0, strrpos($extensionNamespace, '\\') + 1) . 'Domain\\';

        $types = [];
        foreach (new Finder()->files()->name('*DBALType.php')->in($domainDir) as $file) {
            $relative = str_replace([$domainDir . DIRECTORY_SEPARATOR, '.php'], ['', ''], $file->getRealPath());
            $fqcn = $baseNamespace . str_replace(DIRECTORY_SEPARATOR, '\\', $relative);

            if (!class_exists($fqcn)) {
                continue;
            }

            if (new ReflectionClass($fqcn)->isAbstract()) {
                continue;
            }

            $types[$fqcn::getName()] = $fqcn;
        }

        if ($types !== []) {
            $container->prependExtensionConfig('doctrine', ['dbal' => ['types' => $types]]);
        }
    }
}
