<?php

namespace ForkCMS\Modules\Backend\Domain\UserGroup;

use ForkCMS\Core\Domain\Form\TabsType;
use ForkCMS\Modules\Backend\Backend\Actions\AuthenticationLogin;
use ForkCMS\Modules\Backend\Backend\Actions\NotFound as ActionNotFound;
use ForkCMS\Modules\Backend\Backend\Ajax\NotFound as AjaxNotFound;
use ForkCMS\Modules\Backend\Domain\Action\ModuleAction;
use ForkCMS\Modules\Backend\Domain\AjaxAction\ModuleAjaxAction;
use ForkCMS\Modules\Backend\Domain\User\UserDataGridChoiceType;
use ForkCMS\Modules\Backend\Domain\UserGroup\Permission\Permission;
use ForkCMS\Modules\Backend\Domain\UserGroup\Permission\PermissionType;
use ForkCMS\Modules\Backend\Domain\Widget\ModuleWidget;
use ReflectionClass;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

final class UserGroupType extends AbstractType
{
    public function __construct(
        private ServiceLocator $backendActions,
        private ServiceLocator $backendAjaxActions,
        private ServiceLocator $backendDashboardWidgets,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $actions = $this->getAvailableActions();
        $ajaxActions = $this->getAvailableAjaxActions();
        $widgets = $this->getAvailableWidgets();

        $builder->add(
            'userGroup',
            TabsType::class,
            [
                'label' => 'lbl.Name',
                'tabs' => [
                    'lbl.Name' => static function (FormBuilderInterface $builder): void {
                        $builder->add(
                            'name',
                            TextType::class,
                            [
                                'label' => 'lbl.Name',
                                'required' => true,
                            ]
                        );
                    },
                    'lbl.Dashboard' => static function (FormBuilderInterface $builder) use ($widgets): void {
                        $builder->add(
                            'widgets',
                            PermissionType::class,
                            [
                                'choices' => $widgets,
                                'name_label' => 'lbl.Widget',
                                'transform_callback' => static function (array $widgetFQCNs) use ($widgets): array {
                                    $permissions = [];
                                    foreach ($widgetFQCNs as $widgetFQCN) {
                                        $permissions[] = $widgets[$widgetFQCN];
                                    }

                                    return $permissions;
                                }
                            ]
                        );
                    },
                    'lbl.Actions' => function (FormBuilderInterface $builder) use ($actions): void {
                        $builder->add(
                            'actions',
                            PermissionType::class,
                            [
                                'choices' => $actions,
                                'name_label' => 'lbl.Action',
                                'transform_callback' => static function (array $actionFQCNs) use ($actions): array {
                                    $permissions = [];
                                    foreach ($actionFQCNs as $actionFQCN) {
                                        $permissions[] = $actions[$actionFQCN];
                                    }

                                    return $permissions;
                                }
                            ]
                        );
                    },
                    'lbl.AjaxActions' => function (FormBuilderInterface $builder) use ($ajaxActions): void {
                        $builder->add(
                            'ajaxActions',
                            PermissionType::class,
                            [
                                'choices' => $ajaxActions,
                                'name_label' => 'lbl.AjaxActions',
                                'transform_callback' => static function (array $ajaxActionFQCNs) use ($ajaxActions
                                ): array {
                                    $permissions = [];
                                    foreach ($ajaxActionFQCNs as $ajaxActionFQCN) {
                                        $permissions[] = $ajaxActions[$ajaxActionFQCN];
                                    }

                                    return $permissions;
                                }
                            ]
                        );
                    },
                    'lbl.Users' => static function (FormBuilderInterface $builder): void {
                        $builder->add(
                            'users',
                            UserDataGridChoiceType::class,
                            [
                                'required' => false,
                            ]
                        );
                    },
                ]
            ]
        );
    }

    private function getAvailableActions(): array
    {
        $actions = array_map(
            static function (string $fullyQualifiedClassName): Permission {
                $moduleAction = ModuleAction::fromFQCN($fullyQualifiedClassName);

                return new Permission(
                    $fullyQualifiedClassName,
                    $moduleAction->getModule()->getName(),
                    $moduleAction->getAction()->asLabel(),
                    self::getClassDescription($fullyQualifiedClassName),
                );
            },
            $this->backendActions->getProvidedServices()
        );

        unset(
            $actions[AuthenticationLogin::class],
            $actions[ActionNotFound::class],
        );

        return $actions;
    }

    private function getAvailableWidgets(): array
    {
        return array_map(
            static function (string $fullyQualifiedClassName): Permission {
                $moduleAction = ModuleWidget::fromFQCN($fullyQualifiedClassName);

                return new Permission(
                    $fullyQualifiedClassName,
                    $moduleAction->getModule()->getName(),
                    $moduleAction->getWidget()->asLabel(),
                    self::getClassDescription($fullyQualifiedClassName),
                );
            },
            $this->backendDashboardWidgets->getProvidedServices()
        );
    }

    private function getAvailableAjaxActions(): array
    {
        $ajaxActions = array_map(
            static function (string $fullyQualifiedClassName): Permission {
                $moduleAjaxAction = ModuleAjaxAction::fromFQCN($fullyQualifiedClassName);

                return new Permission(
                    $fullyQualifiedClassName,
                    $moduleAjaxAction->getModule()->getName(),
                    $moduleAjaxAction->getAction()->asLabel(),
                    self::getClassDescription($fullyQualifiedClassName),
                );
            },
            $this->backendAjaxActions->getProvidedServices()
        );

        unset(
            $ajaxActions[AjaxNotFound::class],
        );

        return $ajaxActions;
    }

    private static function getClassDescription(string $fullyQualifiedClassName): string
    {
        $reflection = new ReflectionClass($fullyQualifiedClassName);
        $phpDoc = trim($reflection->getDocComment());
        if ($phpDoc === '') {
            return '';
        }

        $offset = mb_strpos($reflection->getDocComment(), '*', 7);
        $description = mb_substr($reflection->getDocComment(), 0, $offset);
        $description = str_replace('*', '', $description);

        return trim(str_replace('/', '', $description));
    }
}
