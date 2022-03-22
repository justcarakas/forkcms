<?php

namespace ForkCMS\Modules\Backend\Backend\Actions;

use ForkCMS\Core\Backend\Domain\Form\DeleteType;
use ForkCMS\Core\Domain\Header\FlashMessage\FlashMessage;
use ForkCMS\Modules\Backend\Domain\Action\AbstractFormActionController;
use ForkCMS\Modules\Backend\Domain\Action\ActionSlug;
use ForkCMS\Modules\Backend\Domain\Action\ModuleAction;
use ForkCMS\Modules\Backend\Domain\UserGroup\Command\ChangeUserGroup;
use ForkCMS\Modules\Backend\Domain\UserGroup\Command\CreateUserGroup;
use ForkCMS\Modules\Backend\Domain\UserGroup\UserGroup;
use ForkCMS\Modules\Backend\Domain\UserGroup\UserGroupType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Edit backend user groups
 */
final class UserGroupEdit extends AbstractFormActionController
{
    protected function getFormResponse(Request $request): ?Response
    {
        /** @var UserGroup $userGroup */
        $userGroup = $this->getEntityFromRequest($request, UserGroup::class);

        $this->setBreadcrumbDetail($userGroup->getName());

        $this->addDeleteForm(['id' => $userGroup->getId()], UserGroupDelete::getActionSlug());

        return $this->handleForm(
            request: $request,
            formType: UserGroupType::class,
            formData: new ChangeUserGroup($userGroup),
            redirectResponse: new RedirectResponse(UserGroupIndex::getActionSlug()->generateRoute($this->router)),
            flashMessageCallback: static function (ChangeUserGroup $changedUserGroup): FlashMessage {
                return FlashMessage::success('Edited', ['entity' => $changedUserGroup->name]);
            }
        );
    }
}
