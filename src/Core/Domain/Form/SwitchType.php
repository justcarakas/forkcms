<?php

namespace ForkCMS\Core\Domain\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/** @extends AbstractType<bool> */
class SwitchType extends AbstractType
{
    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);
        $resolver->setDefault('label_attr', ['class' => 'checkbox-switch']);
    }

    #[\Override]
    public function getParent(): string
    {
        return CheckboxType::class;
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'checkbox_switch';
    }
}
