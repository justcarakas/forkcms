<?php

namespace ForkCMS\Modules\Internationalisation\Domain\Locale;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LocaleType extends AbstractType implements DataTransformerInterface
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer($this);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'choices' => array_flip(Locale::toArray()),
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return 'fork_locale';
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }

    /** @return Locale[]|Locale|null */
    public function transform($value): array|Locale|null
    {
        return $value;
    }

    /** @return Locale[]|Locale|null */
    public function reverseTransform($value): array|Locale|null
    {
        return match (true) {
            empty($value) => $value,
            is_array($value) => array_map(
                static function (string $localeCode) {
                    return Locale::from($localeCode);
                },
                $value
            ),
            default => Locale::from($value),
        };
    }
}
