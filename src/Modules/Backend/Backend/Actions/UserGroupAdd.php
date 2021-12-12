<?php

namespace ForkCMS\Modules\Backend\Backend\Actions;

use ForkCMS\Core\Domain\Header\FlashMessage\FlashMessage;
use ForkCMS\Modules\Backend\Domain\Action\AbstractFormActionController;
use ForkCMS\Modules\Backend\Domain\UserGroup\Command\CreateUserGroup;
use ForkCMS\Modules\Backend\Domain\UserGroup\UserGroupType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Add new user groups to the backend
 */
final class UserGroupAdd extends AbstractFormActionController
{
    protected function getFormResponse(Request $request): ?Response
    {
        return $this->handleForm(
            request: $request,
            formType: UserGroupType::class,
            formData: new CreateUserGroup(),
            flashMessage: FlashMessage::success('Added'),
            redirectResponse: new RedirectResponse(UserGroupIndex::getActionSlug()->generateRoute($this->router))
        );
    }
}
