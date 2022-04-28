<?php

namespace ForkCMS\Core\Domain\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class TabsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        foreach ($options['tabs'] as $label => $fields) {
            $builder->add(
                md5($label),
                TabType::class,
                [
                    'fields' => $fields,
                    'label' => $label,
                    'inherit_data' => $options['tab_inherit_data'],
                    'attr' => $options['tab_attr'],
                ]
            );
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults(
                [
                    'inherit_data' => true,
                    'tab_inherit_data' => true,
                    'options' => [],
                    'tabs' => [],
                    'label' => false,
                    'tab_attr' => [],
                ]
            )
            ->addAllowedTypes('tabs', 'array');
    }
}
