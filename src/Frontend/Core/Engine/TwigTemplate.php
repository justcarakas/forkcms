<?php

namespace Frontend\Core\Engine;

use Common\ModulesSettings;
use Frontend\Core\Language\Locale;
use Common\Core\Twig\BaseTwigTemplate;
use Common\Core\Twig\Extensions\TwigFilters;
use Symfony\Bridge\Twig\Form\TwigRendererEngine;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Bridge\Twig\Extension\FormExtension as SymfonyFormExtension;
use Symfony\Component\Form\FormRenderer;
use Twig\Loader\ChainLoader;
use Twig\Loader\FilesystemLoader;
use Twig\Loader\LoaderInterface;
use Twig\RuntimeLoader\FactoryRuntimeLoader;

/**
 * This is a twig template wrapper
 * that glues spoon libraries and code standards with twig
 */
class TwigTemplate extends BaseTwigTemplate
{
    /**
     * @var string
     */
    private $themePath;

    public function __construct(
        LoaderInterface $loader
    ) {
        $container = Model::getContainer();
        $this->forkSettings = $container->get(ModulesSettings::class);
        $this->language = Locale::frontendLanguage();

        parent::__construct($loader);

        $this->debugMode = $container->getParameter('kernel.debug');
        if ($this->debugMode) {
            $this->enableAutoReload();
            $this->setCache(false);
        }
        $this->disableStrictVariables();
        new FormExtension($this);
        TwigFilters::addFilters($this, 'Frontend');
        $this->startGlobals();

        if (!$container->getParameter('fork.is_installed')) {
            return;
        }

        $this->addFrontendPathsToTheTemplateLoader($this->forkSettings->get('Core', 'theme', 'Fork'));
        $this->connectSymfonyForms();
    }

    private function addFrontendPathsToTheTemplateLoader(string $theme): void
    {
        $this->themePath = FRONTEND_PATH . '/Themes/' . $theme;
        $this->setLoader(
            new ChainLoader(
                [$this->getLoader(), new FilesystemLoader($this->getLoadingFolders())]
            )
        );
    }

    private function connectSymfonyForms(): void
    {
        $rendererEngine = new TwigRendererEngine($this->getFormTemplates('FormLayout.html.twig'), $this);
        $csrfTokenManager = Model::get('security.csrf.token_manager');
        $this->addRuntimeLoader(
            new FactoryRuntimeLoader(
                [
                    FormRenderer::class => function () use ($rendererEngine, $csrfTokenManager): FormRenderer {
                        return new FormRenderer($rendererEngine, $csrfTokenManager);
                    },
                ]
            )
        );

        if (!$this->hasExtension(SymfonyFormExtension::class)) {
            $this->addExtension(new SymfonyFormExtension());
        }
    }

    /**
     * Convert a filename extension
     *
     * @param string $template
     *
     * @return string
     */
    public function getPath(string $template): string
    {
        if (strpos($template, FRONTEND_MODULES_PATH) !== false) {
            return str_replace(FRONTEND_MODULES_PATH . '/', '', $template);
        }

        // else it's in the theme folder
        return str_replace($this->themePath . '/', '', $template);
    }

    /**
     * Fetch the parsed content from this template.
     *
     * @param string $template The location of the template file, used to display this template.
     *
     * @return string The actual parsed content after executing this template.
     */
    public function getContent(string $template): string
    {
        $template = $this->getPath($template);

        $content = $this->render(
            $template,
            $this->variables
        );

        $this->variables = [];

        return $content;
    }

    private function getLoadingFolders(): array
    {
        return $this->filterOutNonExistingPaths(
            [
                $this->themePath . '/Modules',
                $this->themePath,
                FRONTEND_MODULES_PATH,
                FRONTEND_PATH,
            ]
        );
    }

    private function getFormTemplates(string $fileName): array
    {
        return $this->filterOutNonExistingPaths(
            [
                FRONTEND_PATH . '/Core/Layout/Templates/' . $fileName,
                $this->themePath . '/Core/Layout/Templates/' . $fileName,
            ]
        );
    }

    private function filterOutNonExistingPaths(array $files): array
    {
        $filesystem = new Filesystem();

        return array_filter(
            $files,
            function ($folder) use ($filesystem) {
                return $filesystem->exists($folder);
            }
        );
    }
}
