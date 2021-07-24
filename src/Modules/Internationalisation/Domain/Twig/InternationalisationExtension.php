<?php

namespace ForkCMS\Modules\Internationalisation\Domain\Twig;

use ForkCMS\Modules\Internationalisation\Domain\Translation\TranslationKey;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

final class InternationalisationExtension extends AbstractExtension
{
    public function __construct(private TranslatorInterface $translator)
    {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter(
                'tolabel',
                static fn (string $string): string => TranslationKey::label($string)->trans($this->translator)
            )
        ];
    }
}
