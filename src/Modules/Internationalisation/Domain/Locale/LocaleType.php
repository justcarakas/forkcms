<?php

namespace ForkCMS\Modules\Internationalisation\Domain\Locale;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LocaleType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'class' => Locale::class,
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return 'fork_locale';
    }

    public function getParent(): string
    {
        return EnumType::class;
    }
}
