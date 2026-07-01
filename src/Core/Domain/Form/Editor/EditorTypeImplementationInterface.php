<?php

declare(strict_types=1);

namespace ForkCMS\Core\Domain\Form\Editor;

use ForkCMS\Modules\Internationalisation\Domain\Translation\TranslationKey;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag(self::class)]
interface EditorTypeImplementationInterface
{
    public function getLabel(): TranslationKey;

    public function parseContent(string $content): string;
}
