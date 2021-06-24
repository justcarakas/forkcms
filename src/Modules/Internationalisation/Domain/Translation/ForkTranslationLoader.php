<?php

namespace ForkCMS\Modules\Internationalisation\Domain\Translation;

use ForkCMS\Modules\Internationalisation\Domain\Locale\Locale;
use Symfony\Component\Translation\Loader\LoaderInterface;
use Symfony\Component\Translation\MessageCatalogue;

final class ForkTranslationLoader implements LoaderInterface
{
    public function __construct(private TranslationRepository $translationRepository)
    {
    }

    public function load($resource, string $locale, string $domain = 'messages'): MessageCatalogue
    {
        $translationDomain = TranslationDomain::fromDomain($domain);
        $forkLocale = Locale::from($locale);
        $catalogue = new MessageCatalogue($forkLocale);
        foreach (
            $this->translationRepository->findBy(
                [
                    'locale' => $forkLocale,
                    'domain.application' => $translationDomain->getApplication(),
                    'domain.moduleName' => $translationDomain->getModuleName(),
                ]
            ) as $translation
        ) {
            $catalogue->set((string) $translation->getKey(), $translation->getValue(), $domain);
        }

        return $catalogue;
    }
}
