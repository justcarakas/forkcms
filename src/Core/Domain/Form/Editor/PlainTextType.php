<?php

declare(strict_types=1);

namespace ForkCMS\Core\Domain\Form\Editor;

use ForkCMS\Modules\Internationalisation\Domain\Translation\TranslationKey;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

/** @extends AbstractType<string> */
final class PlainTextType extends AbstractType implements EditorTypeImplementationInterface
{
    #[\Override]
    public function getParent(): string
    {
        return TextareaType::class;
    }

    public function getLabel(): TranslationKey
    {
        return TranslationKey::label('Text');
    }

    public function parseContent(string $content): string
    {
        return $content;
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'plain_text_editor';
    }
}
