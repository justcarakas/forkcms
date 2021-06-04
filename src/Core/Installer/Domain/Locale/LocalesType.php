<?php

namespace ForkCMS\Core\Installer\Domain\Locale;

use ForkCMS\Core\Domain\Locale\LocaleType;
use InvalidArgumentException;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Builds the form to select locales to install
 */
class LocalesType extends AbstractType implements DataTransformerInterface
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'multilingual',
            ChoiceType::class,
            [
                'expanded' => true,
                'multiple' => false,
                'choices' => [
                    'Just one locale' => false,
                    'Multiple locales' => true,
                ],
                'choice_attr' => static function (bool $multilingual) {
                    return [
                        'data-fork-cms-role' => $multilingual ? 'multilingual' : 'not-multilingual',
                    ];
                },
            ]
        )->add(
            'locales',
            LocaleType::class,
            [
                'expanded' => true,
                'multiple' => true,
                'attr' => [
                    'data-fork-cms-role' => 'locales',
                ],
            ]
        )->add(
            'defaultLocale',
            LocaleType::class,
            [
                'attr' => [
                    'data-fork-cms-role' => 'default-locale',
                ],
            ]
        )->add(
            'sameInterfaceLocale',
            CheckboxType::class,
            [
                'label' => 'Use the same locale(s) for the CMS interface.',
                'required' => false,
                'attr' => [
                    'data-fork-cms-role' => 'same-interface-locale',
                ],
            ]
        )->add(
            'defaultInterfaceLocale',
            LocaleType::class,
            [
                'attr' => [
                    'data-fork-cms-role' => 'default-interface-locale',
                ],
            ]
        )->add(
            'interfaceLocales',
            LocaleType::class,
            [
                'multiple' => true,
                'expanded' => true,
                'attr' => [
                    'data-fork-cms-role' => 'interface-locales',
                ],
            ]
        )->addModelTransformer($this);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => LocalesStepConfiguration::class,
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return 'install_locales';
    }

    public function transform($value): LocalesStepConfiguration
    {
        return $value;
    }

    public function reverseTransform($value): LocalesStepConfiguration
    {
        if (!$value instanceof LocalesStepConfiguration) {
            throw new InvalidArgumentException('Only an instance of LocalesStepConfiguration is allowed here');
        }

        $value->normalise();

        return $value;
    }
}
