<?php

namespace ForkCMS\Modules\Internationalisation\DependencyInjection\CompilerPass;

use ForkCMS\Core\Domain\Application\Application;
use ForkCMS\Modules\Extensions\Domain\Module\InstalledModules;
use ForkCMS\Modules\Internationalisation\Domain\Locale\Locale;
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
        $locales = $this->getDatabaseLocales();
        $translator = $container->getDefinition('translator.default');
        $translator->setClass(ForkTranslator::class);
        $translator->addArgument(new Reference(RequestStack::class));
        $container->prependExtensionConfig(
            'framework',
            [
                'translator' => [
                    'enabled_locale' => array_map(
                        static fn(Locale $locale): string => $locale->value,
                        $locales
                    ),
                ],
            ]
        );

        foreach ($domains as $domain) {
            foreach ($locales as $locale) {
                $translator->addMethodCall(
                    'addResource',
                    array(
                        'db',
                        null,
                        $locale->value,
                        $domain->getDomain(),
                    )
                );
            }
        }
    }

    private function getDatabaseDomains(InstalledModules $moduleNames): array
    {
        $applications = Application::cases();
        $translationDomains = [];
        foreach ($moduleNames() as $moduleName) {
            foreach ($applications as $application) {
                $translationDomains[] = new TranslationDomain($application, $moduleName);
            }
        }

        return $translationDomains;
    }

    /** @return Locale[] */
    private function getDatabaseLocales(): array
    {
        return Locale::cases();
    }
}
