<?php

declare(strict_types=1);

namespace ForkCMS\Modules\Internationalisation\Domain\Importer;

use ForkCMS\Modules\Internationalisation\Domain\Translation\Translation;
use Generator;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\HttpFoundation\File\File;

#[AutoconfigureTag(self::class)]
interface ImporterInterface
{
    /** @return Generator<Translation> */
    public function getTranslations(File $translationFile): Generator;
}
