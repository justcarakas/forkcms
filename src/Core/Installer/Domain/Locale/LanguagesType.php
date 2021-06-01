<?php

namespace ForkCMS\Core\Installer\Domain\Locale;

use ForkCMS\Core\Domain\Locale\Locale;
use ForkCMS\Core\Installer\Domain\Installer\InstallationData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Builds the form to select languages to install
 */
class LanguagesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $availableLocale = array_flip(Locale::toArray());
        $builder
            ->add(
                'language_type',
                ChoiceType::class,
                [
                    'expanded' => true,
                    'multiple' => false,
                    'choices' => [
                        'Just one language' => 'single',
                        'Multiple languages' => 'multiple',
                    ],
                ]
            )
            ->add(
                'languages',
                ChoiceType::class,
                [
                    'choices' => $availableLocale,
                    'expanded' => true,
                    'multiple' => true,
                ]
            )
            ->add(
                'default_language',
                ChoiceType::class,
                [
                    'choices' => $availableLocale,
                ]
            )
            ->add(
                'same_interface_language',
                CheckboxType::class,
                [
                    'label' => 'Use the same language(s) for the CMS interface.',
                    'required' => false,
                ]
            )
            ->add(
                'default_interface_language',
                ChoiceType::class,
                [
                    'choices' => $availableLocale,
                ]
            )
            ->add(
                'interface_languages',
                ChoiceType::class,
                [
                    'choices' => $availableLocale,
                    'multiple' => true,
                    'expanded' => true,
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => InstallationData::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'install_languages';
    }
}
