<?php

namespace ForkCMS\Core\Domain\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class FieldsetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $options['fields']($builder);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults(
                [
                    'inherit_data' => true,
                    'row_attr' => ['class' => 'card card-default'],
                    'label_attr' => ['class' => 'card-header'],
                    'attr' => ['class' => 'card-body'],
                    'fields' => static function (FormBuilderInterface $builder): void {
                    },
                    'label' => false,
                ]
            )
            ->addAllowedTypes('fields', 'callable');
    }
}
