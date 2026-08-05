<?php

declare(strict_types=1);

namespace ForkCMS\Modules\Frontend\tests\Domain\Meta;

use ForkCMS\Core\tests\WebTestCase;
use ForkCMS\Modules\Frontend\Domain\Meta\MetaType;
use ForkCMS\Modules\Pages\Domain\Revision\RevisionRepository;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

final class MetaTypeTest extends WebTestCase
{
    private function createWrappingForm(): FormInterface
    {
        $formFactory = self::getContainer()->get(FormFactoryInterface::class);

        return $formFactory->createBuilder(options: ['csrf_protection' => false])
            ->add('title', TextType::class)
            ->add('meta', MetaType::class, [
                'base_field_name' => 'title',
                'custom_meta_tags' => false,
                'generate_slug_callback_class' => RevisionRepository::class,
                'generate_slug_callback_method' => 'slugify',
                'base_url' => '/',
            ])
            ->getForm();
    }

    public function testMetaWidgetRendersTheSlugGeneratorComponent(): void
    {
        $html = self::getContainer()->get('twig')
            ->createTemplate("{% form_theme form '@Backend/base/formTheme.html.twig' %}{{ form(form) }}")
            ->render(['form' => $this->createWrappingForm()->createView()]);

        self::assertStringContainsString('data-controller="backend--slug-generator live"', $html);
        self::assertStringContainsString('data-backend--slug-generator-base-field-selector-value="#form_title"', $html);
        self::assertStringContainsString(
            'data-backend--slug-generator-overwrite-selector-value="#form_meta_slugOverwrite"',
            $html
        );
        self::assertStringContainsString('data-backend--slug-generator-input-selector-value="#form_meta_slug"', $html);
        self::assertStringContainsString(
            'data-backend--slug-generator-preview-selector-value="#generatedSlug"',
            $html
        );
    }
}
