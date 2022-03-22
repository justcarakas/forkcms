<?php

namespace ForkCMS\Modules\Backend\Backend\Actions;

use ForkCMS\Core\Domain\Header\FlashMessage\FlashMessage;
use ForkCMS\Modules\Backend\Domain\Action\AbstractFormActionController;
use ForkCMS\Modules\Backend\Domain\Action\ActionSlug;
use ForkCMS\Modules\Backend\Domain\User\Command\ChangeUser;
use ForkCMS\Modules\Backend\Domain\User\User;
use ForkCMS\Modules\Backend\Domain\User\UserType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Edit backend users
 */
final class UserEdit extends AbstractFormActionController
{
    protected function getFormResponse(Request $request): ?Response
    {
        /** @var User $user */
        $user = $this->getEntityFromRequest($request, User::class);

        $this->setBreadcrumbDetail($user->getDisplayName());

        if ($this->getRepository(User::class)->count([]) > 1) {
            $this->addDeleteForm(['id' => $user->getId()], UserDelete::getActionSlug());
        }

        return $this->handleForm(
            request: $request,
            formType: UserType::class,
            formData: new ChangeUser($user),
            redirectResponse: new RedirectResponse(UserIndex::getActionSlug()->generateRoute($this->router)),
            flashMessageCallback: static function (ChangeUser $changedUser): FlashMessage {
                return FlashMessage::success('Edited', ['entity' => $changedUser->name]);
            }
        );
    }
}
