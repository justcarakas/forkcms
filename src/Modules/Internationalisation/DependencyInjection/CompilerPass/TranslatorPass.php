<?php

namespace ForkCMS\Modules\Internationalisation\DependencyInjection\CompilerPass;

use ForkCMS\Core\Domain\Application\Application;
use ForkCMS\Core\Domain\PDO\ForkConnection;
use ForkCMS\Modules\Extensions\Domain\Module\InstalledModules;
use ForkCMS\Modules\Internationalisation\Domain\Locale\Locale;
use ForkCMS\Modules\Internationalisation\Domain\Translation\DataCollectorTranslator;
use ForkCMS\Modules\Internationalisation\Domain\Translation\TranslationDomain;
use ForkCMS\Modules\Internationalisation\Domain\Translator\ForkTranslator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpFoundation\RequestStack;

final class TranslatorPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $domains = $this->getDatabaseDomains(InstalledModules::fromContainer($container));
        $locales = $this->getDatabaseLocales($container);
        $defaultLocale = array_search(true, $locales);
        $translator = $container->getDefinition('translator.default');
        $translator->setClass(ForkTranslator::class);
        $translator->addArgument(new Reference(RequestStack::class));
        $container->prependExtensionConfig(
            'framework',
            [
                'default_locale' => $defaultLocale,
                'translator' => [
                    'enabled_locale' => array_keys($locales),
                ],
            ]
        );

        foreach ($domains as $domain) {
            foreach ($locales as $locale => $isDefault) {
                $translator->addMethodCall(
                    'addResource',
                    array(
                        'db',
                        null,
                        $locale,
                        $domain->getDomain(),
                    )
                );
            }
        }

        if ($container->hasDefinition('translator.data_collector')) {
            $container->getDefinition('translator.data_collector')->setClass(DataCollectorTranslator::class);
        }
    }

    private function getDatabaseDomains(InstalledModules $moduleNames): array
    {
        $applications = Application::cases();
        $translationDomains = [];
        foreach ($moduleNames() as $moduleName) {
            foreach ($applications as $application) {
                $translationDomain = new TranslationDomain($application, $moduleName);
                $translationDomains[$translationDomain->getDomain()] = $translationDomain;
                $fallbackTranslationDomain = $translationDomain->getFallback();
                if ($fallbackTranslationDomain !== null) {
                    $translationDomains[$fallbackTranslationDomain->getDomain()] = $fallbackTranslationDomain;
                }
            }
        }

        return $translationDomains;
    }

    /** @return array<string, bool> */
    private function getDatabaseLocales(ContainerBuilder $container): array
    {
        if (!$container->getParameter('fork.is_installed')) {
            $locales[Locale::en()->value] = true;

            return $locales;
        }

        return ForkConnection::get()->getEnabledLocales();
    }
}
