<?php

namespace ForkCMS\Modules\Internationalisation\Domain\Translator;

use BadMethodCallException;
use ForkCMS\Core\Domain\Application\Application;
use ForkCMS\Core\Domain\Util\Ensure;
use ForkCMS\Modules\Backend\Domain\Action\ActionSlug;
use ForkCMS\Modules\Backend\Domain\AjaxAction\AjaxActionSlug;
use ForkCMS\Modules\Backend\Domain\User\User;
use ForkCMS\Modules\Internationalisation\Domain\Translation\TranslationDomain;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Translation\MessageCatalogueInterface;
use Symfony\Component\Translation\TranslatorBagInterface;
use Symfony\Contracts\Translation\LocaleAwareInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use ValueError;

/** This class will make sure that the domain is set correctly */
final class ForkTranslator implements TranslatorInterface, TranslatorBagInterface, LocaleAwareInterface
{
    private ?TranslationDomain $defaultTranslationDomain = null;

    private ?string $fallbackLocale = null;

    public function __construct(
        private readonly TranslatorInterface&TranslatorBagInterface $inner,
        private readonly ?Security $security = null,
        private readonly ?RequestStack $requestStack = null,
    ) {
    }

    /** @param array<string, mixed> $parameters */
    #[\Override]
    public function trans(string $id, array $parameters = [], ?string $domain = null, ?string $locale = null): string
    {
        $isValidator = $domain === 'validators';
        if ($isValidator) {
            $domain = null;
        }

        if ($this->fallbackLocale === null) {
            $user = $this->security?->getUser();
            $this->fallbackLocale = ($user instanceof User ? $user->getSetting('locale') : null) ?? $this->getLocale();
        }
        $locale ??= $this->fallbackLocale;

        if (!$this->requestStack instanceof RequestStack) {
            return $this->innerTrans($id, $parameters, $domain, $locale);
        }

        if ($this->defaultTranslationDomain === null) {
            $this->defaultTranslationDomain = $this->determineDefaultTranslationDomain();
        }

        $domain ??= $this->defaultTranslationDomain->getDomain();

        return $this->innerTrans($id, $parameters, $this->resolveDomain($id, $domain, $locale, $isValidator), $locale);
    }

    private function resolveDomain(string $id, string $domain, ?string $locale, bool $isValidator): string
    {
        $catalogue = $this->inner->getCatalogue($locale);

        if ($catalogue->has($id, $domain)) {
            return $domain;
        }

        try {
            $fallback = TranslationDomain::fromDomain($domain)->getFallback();
        } catch (ValueError | InvalidArgumentException | BadMethodCallException) {
            return $isValidator && $catalogue->has($id, 'validator') ? 'validator' : $domain;
        }

        if ($fallback !== null && $catalogue->has($id, $fallback->getDomain())) {
            return $fallback->getDomain();
        }

        if ($isValidator && $catalogue->has($id, 'validator')) {
            return 'validator';
        }

        return $domain;
    }

    public function setDefaultTranslationDomain(TranslationDomain $defaultTranslationDomain): void
    {
        $this->defaultTranslationDomain = $defaultTranslationDomain;
    }

    public function hasTranslation(string $id, ?string $locale = null): bool
    {
        if (!$this->requestStack instanceof RequestStack) {
            return false;
        }

        $defaultDomain = $this->getDefaultTranslationDomain();
        $catalogue = $this->inner->getCatalogue($locale);

        if ($catalogue->has($id, $defaultDomain->getDomain())) {
            return true;
        }

        $fallback = $defaultDomain->getFallback();

        return $fallback !== null && $catalogue->has($id, $fallback->getDomain());
    }

    public function getDefaultTranslationDomain(): TranslationDomain
    {
        if ($this->defaultTranslationDomain === null) {
            $this->setDefaultTranslationDomain($this->determineDefaultTranslationDomain());
        }

        return $this->defaultTranslationDomain;
    }

    #[\Override]
    public function getLocale(): string
    {
        return $this->inner->getLocale();
    }

    #[\Override]
    public function setLocale(string $locale): void
    {
        if ($this->inner instanceof LocaleAwareInterface) {
            $this->inner->setLocale($locale);
        }
    }

    public function getCatalogue(?string $locale = null): MessageCatalogueInterface
    {
        return $this->inner->getCatalogue($locale);
    }

    /** @return MessageCatalogueInterface[] */
    public function getCatalogues(): array
    {
        return $this->inner->getCatalogues();
    }

    /** @param array<string, mixed> $parameters */
    private function innerTrans(string $id, array $parameters, ?string $domain, ?string $locale): string
    {
        return $this->inner->trans($id, $parameters, $domain, $locale);
    }

    private function determineDefaultTranslationDomain(): TranslationDomain
    {
        $mainRequest = Ensure::isNotNull($this->requestStack)->getMainRequest();
        if ($mainRequest instanceof Request) {
            if ($mainRequest->attributes->has('_locale_application')) {
                $application = Application::tryFrom($mainRequest->attributes->get('_locale_application'));
                if ($application instanceof Application) {
                    return new TranslationDomain($application);
                }
            }
            return match ($mainRequest->attributes->get('_route')) {
                'backend_action',
                'backend_login' => ActionSlug::fromRequest($mainRequest)->getTranslationDomain(),
                'backend_ajax' => AjaxActionSlug::fromRequest($mainRequest)->getTranslationDomain(),
                default => new TranslationDomain(Application::FRONTEND),
            };
        }

        return new TranslationDomain(Application::CONSOLE);
    }
}
