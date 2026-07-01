<?php

declare(strict_types=1);

namespace ForkCMS\Modules\Backend\Backend\Actions;

use ForkCMS\Modules\Backend\Domain\Action\AbstractActionController;
use ForkCMS\Modules\Backend\Domain\NavigationItem\NavigationItemRepository;
use ForkCMS\Modules\Backend\Domain\User\UserRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * A placeholder for the route used by symfony to log a backend user in.
 */
final class AuthenticationLogin extends AbstractActionController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly NavigationItemRepository $navigationItemRepository,
        protected readonly RouterInterface $router,
        private readonly NotFound $notFoundAction,
    ) {
        // no need to call the parent since we don't use it
    }

    #[\Override]
    public function __invoke(Request $request): Response
    {
        try {
            $navigationItem = $this->navigationItemRepository->findFirstWithSlugForUser(
                $this->userRepository->getAuthenticatedUser()
            );
        } catch (AccessDeniedException) {
            return ($this->notFoundAction)($request);
        }

        return new RedirectResponse($navigationItem->slug?->generateRoute($this->router));
    }

    #[\Override]
    protected function execute(Request $request): void
    {
        // everything is handled in the __invoke function
    }
}
