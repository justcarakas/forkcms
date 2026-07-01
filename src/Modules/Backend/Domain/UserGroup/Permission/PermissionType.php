<?php

declare(strict_types=1);

namespace ForkCMS\Modules\Backend\Domain\UserGroup\Permission;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/** @extends AbstractType<array<string, mixed>> */
final class PermissionType extends AbstractType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(
            new CallbackTransformer(
                $options['transform_callback'],
                static function (array $permissions) {
                    return array_map(
                        static fn (Permission $permission): string => $permission->value,
                        $permissions
                    );
                }
            )
        );
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);
        $resolver->setAllowedTypes('choices', Permission::class . '[]');
        $resolver->setRequired('transform_callback');
        $resolver->setAllowedTypes('transform_callback', 'callable');
        $resolver->setDefaults(
            [
                'name_label' => 'lbl.Name',
                'description_label' => 'lbl.Description',
                'label' => false,
                'multiple' => true,
                'expanded' => true,
                'choice_value' => static function (string $permission): string {
                    return (string) $permission;
                },
                'choice_label' => static function (Permission $permission): string {
                    return $permission->name;
                },
            ]
        );
    }

    #[\Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        parent::buildView($view, $form, $options);

        $permissionsByModule = [];
        foreach (array_values($options['choices']) as $index => $permission) {
            $permissionsByModule[$permission->module][$index] = $permission;
        }
        $view->vars['permissionsByModule'] = $permissionsByModule;
        $view->vars['nameLabel'] = $options['name_label'];
        $view->vars['descriptionLabel'] = $options['description_label'];
    }

    #[\Override]
    public function getParent(): string
    {
        return ChoiceType::class;
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'user_group_permission';
    }
}
