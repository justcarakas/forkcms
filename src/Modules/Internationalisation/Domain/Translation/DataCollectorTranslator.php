<?php

namespace ForkCMS\Modules\Internationalisation\Domain\Translation;

use ForkCMS\Modules\Internationalisation\Domain\Translator\ForkTranslator;
use Symfony\Component\Translation\DataCollectorTranslator as SymfonyDataCollectorTranslator;
use Symfony\Contracts\Translation\TranslatorInterface;

final class DataCollectorTranslator extends SymfonyDataCollectorTranslator
{
    private array $messages = [];

    public function __construct(private TranslatorInterface $translator)
    {
        parent::__construct($this->translator);
    }

    /**
     * {@inheritdoc}
     */
    public function trans(?string $id, array $parameters = [], string $domain = null, string $locale = null): string
    {
        $trans = $this->translator->trans($id = (string) $id, $parameters, $domain, $locale);

        if ($this->translator instanceof ForkTranslator) {
            $domain = $this->translator->getLastUsedDomain();
        }

        $this->collectMessage($locale, $domain, $id, $trans, $parameters);

        return $trans;
    }

    /**
     * @return array
     */
    public function getCollectedMessages(): array
    {
        return $this->messages;
    }

    private function collectMessage(
        ?string $locale,
        ?string $domain,
        string $id,
        string $translation,
        ?array $parameters = []
    ): void {
        if (null === $domain) {
            $domain = 'messages';
        }

        $catalogue = $this->translator->getCatalogue($locale);
        $locale = $catalogue->getLocale();
        $fallbackLocale = null;
        if ($catalogue->defines($id, $domain)) {
            $state = self::MESSAGE_DEFINED;
        } elseif ($catalogue->has($id, $domain)) {
            $state = self::MESSAGE_EQUALS_FALLBACK;

            $fallbackCatalogue = $catalogue->getFallbackCatalogue();
            while ($fallbackCatalogue) {
                if ($fallbackCatalogue->defines($id, $domain)) {
                    $fallbackLocale = $fallbackCatalogue->getLocale();
                    break;
                }
                $fallbackCatalogue = $fallbackCatalogue->getFallbackCatalogue();
            }
        } else {
            $state = self::MESSAGE_MISSING;
        }

        $this->messages[] = [
            'locale' => $locale,
            'fallbackLocale' => $fallbackLocale,
            'domain' => $domain,
            'id' => $id,
            'translation' => $translation,
            'parameters' => $parameters,
            'state' => $state,
            'transChoiceNumber' => isset($parameters['%count%']) && is_numeric(
                $parameters['%count%']
            ) ? $parameters['%count%'] : null,
        ];
    }
}
