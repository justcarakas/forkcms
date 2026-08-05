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
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * This form type is a workaround for showing a datagrid in a form.
 *
 * Either "data_grid" (build and render it now) or "lazy_src" (render a placeholder turbo-frame that
 * fetches the real thing from that URL once it's actually shown - see LazyDataGridFieldResolver) must
 * be set, never both: building a DataGrid runs its underlying query immediately, so a form type
 * deciding to defer that shouldn't have already paid for it just to construct this field.
 *
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
        $resolver->setDefaults(
            [
                'data_grid' => null,
                'lazy_src' => null,
                'data_grid_action' => null,
                'data_grid_action_parameters' => [],
                'data_grid_action_locale' => null,
                'data_grid_action_label' => TranslationKey::label('Add'),
                'data_grid_action_class' => 'btn btn-success',
                'data_grid_action_icon' => 'fa fa-plus-square',
                'mapped' => false,
            ]
        );
        $resolver->setAllowedTypes('data_grid', [DataGrid::class, 'null']);
        $resolver->setAllowedTypes('lazy_src', ['string', 'null']);
        $resolver->setAllowedTypes('data_grid_action', [ActionSlug::class, 'null']);
        $resolver->setAllowedTypes('data_grid_action_parameters', 'array');
        $resolver->setAllowedTypes('data_grid_action_locale', [Locale::class, 'null']);
        $resolver->setAllowedTypes('data_grid_action_label', [TranslationKey::class, 'string', 'null']);
        $resolver->setAllowedTypes('data_grid_action_class', ['string']);
        $resolver->setAllowedTypes('data_grid_action_icon', ['string', 'null']);
        $resolver->setNormalizer(
            'lazy_src',
            static function (Options $options, ?string $lazySrc): ?string {
                if ($options['data_grid'] === null && $lazySrc === null) {
                    throw new InvalidOptionsException('Either "data_grid" or "lazy_src" must be set.');
                }
                if ($options['data_grid'] !== null && $lazySrc !== null) {
                    throw new InvalidOptionsException('Only one of "data_grid" or "lazy_src" can be set.');
                }

                return $lazySrc;
            }
        );
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['data_grid'] = $options['data_grid'];
        $view->vars['lazy_src'] = $options['lazy_src'];
        if ($options['data_grid'] === null) {
            return;
        }

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
