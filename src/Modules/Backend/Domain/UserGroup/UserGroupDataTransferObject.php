<?php

namespace ForkCMS\Modules\Backend\Domain\UserGroup;

use Doctrine\Common\Collections\ArrayCollection;
use ForkCMS\Core\Domain\Doctrine\CollectionHelper;
use ForkCMS\Core\Domain\Form\Validator\UniqueDataTransferObject;
use ForkCMS\Core\Domain\Form\Validator\UniqueDataTransferObjectInterface;
use ForkCMS\Core\Domain\Settings\SettingsBag;
use ForkCMS\Modules\Backend\Domain\Action\ModuleAction;
use ForkCMS\Modules\Backend\Domain\AjaxAction\ModuleAjaxAction;
use ForkCMS\Modules\Backend\Domain\User\User;
use ForkCMS\Modules\Backend\Domain\Widget\ModuleWidget;

/** @implements UniqueDataTransferObjectInterface<UserGroup> */
#[UniqueDataTransferObject(fields: ['name'], entityClass: UserGroup::class)]
abstract class UserGroupDataTransferObject implements UniqueDataTransferObjectInterface
{
    public ?string $name;

    /** @var ArrayCollection<int|string, User> */
    public ArrayCollection $users;

    public SettingsBag $settings;

    /** @var string[] */
    public array $actions;

    /** @var string[] */
    public array $ajaxActions;

    /** @var string[] */
    public array $widgets;

    public function __construct(protected ?UserGroup $userGroupEntity = null)
    {
        $this->name = $userGroupEntity?->name;
        $this->users = CollectionHelper::toArrayCollection($userGroupEntity?->users);
        // @phpstan-ignore nullsafe.neverNull
        $this->settings = $userGroupEntity?->settings ?? new SettingsBag();
        // @phpstan-ignore nullsafe.neverNull
        $roles = $userGroupEntity?->roles ?? [];
        $this->actions = array_map(
            static fn (ModuleAction $moduleAjaxAction): string => $moduleAjaxAction->getFQCN(),
            array_filter(array_map(ModuleAction::tryFromRole(...), $roles))
        );
        $this->ajaxActions = array_map(
            static fn (ModuleAjaxAction $moduleAjaxAction): string => $moduleAjaxAction->getFQCN(),
            array_filter(array_map(ModuleAjaxAction::tryFromRole(...), $roles))
        );
        $this->widgets = array_map(
            static fn (ModuleWidget $moduleAjaxAction): string => $moduleAjaxAction->getFQCN(),
            array_filter(array_map(ModuleWidget::tryFromRole(...), $roles))
        );
    }

    #[\Override]
    final public function hasEntity(): bool
    {
        return $this->userGroupEntity !== null;
    }

    #[\Override]
    final public function getEntity(): UserGroup
    {
        return $this->userGroupEntity;
    }
}
