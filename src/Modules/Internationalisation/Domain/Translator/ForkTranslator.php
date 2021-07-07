<?php

namespace ForkCMS\Modules\Internationalisation\Domain\Translator;

use BadMethodCallException;
use ForkCMS\Core\Domain\Application\Application;
use ForkCMS\Modules\Backend\Domain\Action\ActionSlug;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleName;
use ForkCMS\Modules\Internationalisation\Domain\Translation\TranslationDomain;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Translation\Formatter\MessageFormatterInterface;
use TypeError;

/** This class will make sure that the domain is set correctly */
final class ForkTranslator extends Translator
{
    private ?TranslationDomain $defaultTranslationDomain = null;

    /** @var ?string used for debug reasons */
    private ?string $lastUsedDomain;

    /**
     * @param array<string, array<int, string>>$loaderIds
     * @param array<string, mixed>$options
     * @param string[] $enabledLocales
     */
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

    /** @param array<string, mixed> $parameters */
    public function trans(?string $id, array $parameters = [], string $domain = null, string $locale = null): string
    {
        if (!$this->requestStack instanceof RequestStack) {
            return $this->getTranslationAndStoreDomain($id, $parameters, $domain, $locale);
        }

        if ($domain === null && $this->defaultTranslationDomain === null) {
            $mainRequest = $this->requestStack->getMainRequest();
            if ($mainRequest instanceof Request) {
                $this->defaultTranslationDomain = match ($mainRequest->get('_route')) {
                    'backend',
                    'backend_ajax',
                    'backend_login' => ActionSlug::fromRequest($mainRequest)->getTranslationDomain(),
                    default => new TranslationDomain(Application::frontend()),
                };
            }
        }

        $domain ??= $this->defaultTranslationDomain->getDomain();

        $translated = $this->getTranslationAndStoreDomain($id, $parameters, $domain, $locale);

        if ($translated !== $id) {
            return $translated;
        }

        try {
            $fallbackDomain = TranslationDomain::fromDomain($domain)->getFallback();
        } catch (TypeError | InvalidArgumentException | BadMethodCallException) {
            // Not a fork translation domain or no fallback available
            return $translated;
        }

        if ($fallbackDomain === null) {
            return $translated;
        }

        $domain = $fallbackDomain->getDomain();

        // use the fallback of the application
        return $this->getTranslationAndStoreDomain($id, $parameters, $domain, $locale, false);
    }

    public function setDefaultTranslationDomain(TranslationDomain $defaultTranslationDomain): void
    {
        $this->defaultTranslationDomain = $defaultTranslationDomain;
    }

    public function getLastUsedDomain(): ?string
    {
        return $this->lastUsedDomain;
    }

    /** @param array<string, mixed> $parameters */
    private function getTranslationAndStoreDomain(
        ?string $id,
        array $parameters = [],
        string $domain = null,
        string $locale = null,
        bool $storeDomainIfTranslationWasNotFound = true
    ): string {
        $translated = parent::trans($id, $parameters, $domain, $locale);

        if ($storeDomainIfTranslationWasNotFound || $id !== $translated) {
            $this->lastUsedDomain = $domain;
        }

        return $translated;
    }
}
