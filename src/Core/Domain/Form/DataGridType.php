<?php

declare(strict_types=1);

namespace ForkCMS\Core\Domain\Form;

use ForkCMS\Modules\Backend\Domain\Action\ActionSlug;
use ForkCMS\Modules\Internationalisation\Domain\Locale\Locale;
use ForkCMS\Modules\Internationalisation\Domain\Translation\TranslationKey;
use Pageon\DoctrineDataGridBundle\DataGrid\DataGrid;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * This form type is a workaround for showing a datagrid in a form.
 * @extends AbstractType<array<string, mixed>>
 */
final class DataGridType extends AbstractType
{
    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'data_grid';
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('data_grid');
        $resolver->setDefaults(
            [
                'data_grid_action' => null,
                'data_grid_action_parameters' => [],
                'data_grid_action_locale' => null,
                'data_grid_action_label' => TranslationKey::label('Add'),
                'data_grid_action_class' => 'btn btn-success',
                'data_grid_action_icon' => 'fa fa-plus-square',
                'mapped' => false,
            ]
        );
        $resolver->setAllowedTypes('data_grid', DataGrid::class);
        $resolver->setAllowedTypes('data_grid_action', [ActionSlug::class, 'null']);
        $resolver->setAllowedTypes('data_grid_action_parameters', 'array');
        $resolver->setAllowedTypes('data_grid_action_locale', [Locale::class, 'null']);
        $resolver->setAllowedTypes('data_grid_action_label', [TranslationKey::class, 'string', 'null']);
        $resolver->setAllowedTypes('data_grid_action_class', ['string']);
        $resolver->setAllowedTypes('data_grid_action_icon', ['string', 'null']);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['data_grid'] = $options['data_grid'];
        if ($options['data_grid_action'] instanceof ActionSlug) {
            $view->vars['data_grid_action'] = $options['data_grid_action']->actionName->name;
            $view->vars['data_grid_module'] = $options['data_grid_action']->moduleName->name;
        }
        $view->vars['data_grid_action_parameters'] = $options['data_grid_action_parameters'];
        $view->vars['data_grid_action_locale'] = $options['data_grid_action_locale'];
        $view->vars['data_grid_action_label'] = $options['data_grid_action_label'];
        $view->vars['data_grid_action_class'] = $options['data_grid_action_class'];
        $view->vars['data_grid_action_icon'] = $options['data_grid_action_icon'];
    }
}
