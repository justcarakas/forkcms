<?php

namespace ForkCMS\Modules\Extensions\DependencyInjection\CompilerPass;

use ForkCMS\Core\Domain\PDO\ForkConnection;
use ForkCMS\Modules\Extensions\Domain\Module\InstalledModules;
use ForkCMS\Modules\Extensions\Domain\ThemeTemplate\ThemeTemplate;
use ForkCMS\Modules\Installer\Domain\Configuration\InstallerConfiguration;
use PDOException;
use RuntimeException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

final class TwigExtensionPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $filesystem = new Filesystem();
        $twigLoaderDefinition = $container->hasDefinition('twig.loader.native_filesystem')
            ? $container->getDefinition('twig.loader.native_filesystem') : null;
        if (!$twigLoaderDefinition instanceof Definition) {
            return;
        }

        $modulesDirectory = $container->getParameter('kernel.project_dir') . '/src/Modules/';

        if (!$container->getParameter('fork.is_installed')) {
            $twigLoaderDefinition->addMethodCall(
                'addPath',
                [
                    $modulesDirectory . 'Installer/templates',
                ]
            );
            return;
        }

        $theme = ForkConnection::get()->getActiveTheme();
        $themePath = realpath(__DIR__ . '/../../../../Themes/' . $theme . '/');

        foreach (InstalledModules::fromContainer($container)() as $moduleName) {
            $themeTemplates = $themePath . $moduleName;
            $moduleTemplates = $modulesDirectory . $moduleName . '/templates';
            if ($themePath !== false && $filesystem->exists($themeTemplates)) {
                $twigLoaderDefinition->addMethodCall(
                    'addPath',
                    [
                        $themeTemplates,
                        $moduleName->getName(),
                    ]
                );
            }
            if ($filesystem->exists($moduleTemplates)) {
                $twigLoaderDefinition->addMethodCall(
                    'addPath',
                    [
                        $moduleTemplates,
                        $moduleName->getName(),
                    ]
                );
            }
        }
    }
}
