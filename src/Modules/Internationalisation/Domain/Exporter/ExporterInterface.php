<?php

declare(strict_types=1);

namespace ForkCMS\Modules\Internationalisation\Domain\Exporter;

use ForkCMS\Modules\Internationalisation\Domain\Translation\Translation;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag(self::class)]
interface ExporterInterface
{
    /** @param iterable<Translation> $translations */
    public function exportTranslations(iterable $translations): string;
}
