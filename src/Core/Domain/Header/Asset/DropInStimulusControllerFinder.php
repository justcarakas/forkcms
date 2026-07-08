<?php

declare(strict_types=1);

namespace ForkCMS\Core\Domain\Header\Asset;

use ForkCMS\Core\Domain\Application\Application;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleName;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;
use Symfony\Component\Finder\Finder;

/**
 * Discovers plain (unbundled) Stimulus controllers a module dropped into its own
 * assets/{application}/public/js/controllers/ directory - no webpack rebuild required, since
 * these files are already valid, browser-loadable ES modules and get served as-is by Asset's
 * fallback to a module's public/ folder when nothing matching exists in the webpack output yet.
 *
 * Mirrors the owner-namespaced identifiers webpack-discovered controllers get
 * (see assets/stimulus_namespacing.js): <kebab-module-name>--<controller-name>.
 */
final class DropInStimulusControllerFinder
{
    /** @return array<string, string> Stimulus identifier => controller URL */
    public function find(Application $application): array
    {
        $finder = new Finder();

        try {
            $finder
                ->files()
                ->name('*_controller.{js,mjs}')
                ->in(
                    __DIR__ . '/../../../../../src/Modules/*/assets/' . ucfirst($application->value)
                    . '/public/js/controllers'
                );
        } catch (DirectoryNotFoundException) {
            return [];
        }

        $controllers = [];
        foreach ($finder as $file) {
            $moduleName = ModuleName::fromString(basename(dirname($file->getPath(), 5)));
            $controllerName = preg_replace('/_controller\.m?js$/', '', $file->getFilename());
            $identifier = str_replace('_', '-', Container::underscore((string) $moduleName))
                . '--' . str_replace('_', '-', $controllerName);

            $controllers[$identifier] = (string) Asset::forModule(
                $application,
                $moduleName,
                'js/controllers/' . $file->getFilename()
            );
        }

        return $controllers;
    }
}
