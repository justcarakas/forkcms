<?php

namespace ForkCMS\Modules\Backend\Backend\Actions;

use Assert\Assert;
use Assert\Assertion;
use ForkCMS\Modules\Backend\Domain\Action\AbstractFormActionController;
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
final class GroupEdit extends AbstractFormActionController
{
    protected function getFormResponse(Request $request): ?Response
    {
        /** @var UserGroup $userGroup */
        $userGroup = $this->getEntityFromRequest($request, UserGroup::class);
        return $this->handleForm(
            request: $request,
            formType: UserGroupType::class,
            redirectResponse: new RedirectResponse(GroupIndex::getActionSlug()->generateRoute($this->router)),
            formData: new ChangeUserGroup($userGroup)
        );
    }
}
