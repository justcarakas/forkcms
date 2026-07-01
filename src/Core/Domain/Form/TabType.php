<?php

declare(strict_types=1);

namespace ForkCMS\Core\Domain\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/** @extends AbstractType<array<string, mixed>> */
final class TabType extends AbstractType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $options['fields']($builder);
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults(
                [
                    'inherit_data' => true,
                    'options' => [],
                    'fields' => static function (FormBuilderInterface $builder): void {
                    },
                    'label' => false,
                ]
            )
            ->addAllowedTypes('fields', 'callable');
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        parent::finishView($view, $form, $options);

        $view->vars['isActiveTab'] = $view->vars['name'] ===  array_key_first($form->getParent()?->all() ?? []);
    }
}
