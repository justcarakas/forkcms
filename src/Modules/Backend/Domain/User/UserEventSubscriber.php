<?php

namespace ForkCMS\Modules\Backend\Domain\User;

use Locale;
use LogicException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

final class UserEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly Security $security
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => 'onAuthenticationSuccess',
            LoginFailureEvent::class => 'onAuthenticationFailure',
            KernelEvents::REQUEST => 'onRequest',
        ];
    }

    public function onAuthenticationSuccess(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();

        if (!$user instanceof User) {
            return;
        }

        $user->registerAuthenticationSuccess();
        $this->userRepository->save($user);
    }

    public function onAuthenticationFailure(LoginFailureEvent $event): void
    {
        try {
            $user = $event->getPassport()?->getUser();
        } catch (LogicException) {
            return;
        }

        if (!$user instanceof User) {
            return;
        }

        $user->registerAuthenticationFailure();
        $this->userRepository->save($user);
    }

    public function onRequest(RequestEvent $event): void
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            return;
        }

        Locale::setDefault($user->getSetting('locale', Locale::getDefault()));
    }
}
