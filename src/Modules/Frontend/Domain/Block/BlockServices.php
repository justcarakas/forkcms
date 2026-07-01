<?php

declare(strict_types=1);

namespace ForkCMS\Modules\Frontend\Domain\Block;

use Doctrine\ORM\EntityManagerInterface;
use ForkCMS\Core\Domain\Header\Header;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

final readonly class BlockServices
{
    public function __construct(
        public EntityManagerInterface $entityManager,
        public Environment $twig,
        public TranslatorInterface $translator,
        public Header $header,
        public RouterInterface $router,
        public FormFactoryInterface $formFactory,
        public MessageBusInterface $commandBus,
        public AuthorizationCheckerInterface $authorizationChecker,
    ) {
    }
}
