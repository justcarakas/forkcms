<?php

namespace ForkCMS\Modules\Backend\Backend\Actions;

use ForkCMS\Modules\Backend\Domain\Action\AbstractActionController;
use ForkCMS\Modules\Backend\Domain\NavigationItem\NavigationItemRepository;
use ForkCMS\Modules\Backend\Domain\User\UserRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

final class AuthenticationLogin extends AbstractActionController
{
    public function __construct(
        private UserRepository $userRepository,
        private NavigationItemRepository $navigationItemRepository,
        private RouterInterface $router,
    ) {
        //no need to call the parent since we don't use it
    }

    public function __invoke(Request $request): Response
    {
        $navigationItem = $this->navigationItemRepository->findFirstWithSlugForUser(
            $this->userRepository->getAuthenticatedUser()
        );

        return new RedirectResponse($navigationItem->getSlug()?->generateRoute($this->router));
    }

    protected function execute(Request $request): void
    {
        // everything is handled in the __invoke function
    }
}
