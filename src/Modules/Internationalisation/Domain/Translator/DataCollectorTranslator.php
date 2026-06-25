<?php

namespace ForkCMS\Modules\Internationalisation\Domain\Translator;

use ForkCMS\Modules\Internationalisation\Domain\Translation\TranslationDomain;
use Symfony\Component\Translation\MessageCatalogueInterface;
use Symfony\Component\Translation\TranslatorBagInterface;
use Symfony\Contracts\Translation\LocaleAwareInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class DataCollectorTranslator implements TranslatorInterface, TranslatorBagInterface, LocaleAwareInterface
{
    public function __construct(
        private readonly TranslatorInterface&TranslatorBagInterface $inner,
        private readonly ForkTranslator $forkTranslator,
    ) {
    }

    public function getDefaultTranslationDomain(): TranslationDomain
    {
        return $this->forkTranslator->getDefaultTranslationDomain();
    }

    /** @param array<string, mixed> $parameters */
    public function trans(?string $id, array $parameters = [], ?string $domain = null, ?string $locale = null): string
    {
        return $this->inner->trans((string) $id, $parameters, $domain, $locale);
    }

    public function getLocale(): string
    {
        return $this->inner->getLocale();
    }

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
}