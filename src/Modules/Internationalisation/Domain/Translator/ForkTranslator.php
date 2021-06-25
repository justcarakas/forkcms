<?php

namespace ForkCMS\Modules\Internationalisation\Domain\Translator;

use ForkCMS\Core\Domain\Application\Application;
use ForkCMS\Modules\Backend\Domain\Action\ActionSlug;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleName;
use ForkCMS\Modules\Internationalisation\Domain\Translation\TranslationDomain;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Translation\Formatter\MessageFormatterInterface;

/** This class will make sure that the domain is set correctly */
final class ForkTranslator extends Translator
{
    private ?TranslationDomain $defaultTranslationDomain = null;

    public function __construct(
        ContainerInterface $container,
        MessageFormatterInterface $formatter,
        string $defaultLocale,
        array $loaderIds = [],
        array $options = [],
        array $enabledLocales = [],
        private ?RequestStack $requestStack = null
    ) {
        parent::__construct($container, $formatter, $defaultLocale, $loaderIds, $options, $enabledLocales);
    }

    public function trans(?string $id, array $parameters = [], string $domain = null, string $locale = null): string
    {
        if (!$this->requestStack instanceof RequestStack) {
            return parent::trans($id, $parameters, $domain, $locale);
        }

        if ($domain === null && $this->defaultTranslationDomain === null) {
            $mainRequest = $this->requestStack->getMainRequest();
            if ($mainRequest instanceof Request) {
                $this->defaultTranslationDomain = match ($mainRequest->get('_route')) {
                    'backend', 'backend_ajax' => ActionSlug::fromRequest($mainRequest)->getTranslationDomain(),
                    default => new TranslationDomain(Application::frontend(), ModuleName::fromString('Core')),
                };
            }
        }

        return parent::trans($id, $parameters, $domain ?? $this->defaultTranslationDomain->getDomain(), $locale);
    }

    public function setDefaultTranslationDomain(TranslationDomain $defaultTranslationDomain): void
    {
        $this->defaultTranslationDomain = $defaultTranslationDomain;
    }
}
