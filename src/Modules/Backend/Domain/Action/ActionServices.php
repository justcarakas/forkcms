<?php

namespace ForkCMS\Modules\Backend\Domain\Action;

use Doctrine\ORM\EntityManagerInterface;
use ForkCMS\Core\Domain\Header\Header;
use ForkCMS\Core\Domain\Util\Ensure;
use ForkCMS\Modules\Internationalisation\Domain\Translator\ForkTranslator;
use Pageon\DoctrineDataGridBundle\DataGrid\DataGridFactory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

final readonly class ActionServices
{
    public ForkTranslator $translator;

    public function __construct(
        public DataGridFactory $dataGridFactory,
        public EntityManagerInterface $entityManager,
        public Environment $twig,
        TranslatorInterface $translator,
        public Header $header,
        public RouterInterface $router,
        public FormFactoryInterface $formFactory,
        public MessageBusInterface $commandBus,
        public EventDispatcherInterface $eventDispatcher,
        public AuthorizationCheckerInterface $authorizationChecker,
    ) {
        $this->translator = Ensure::isInstanceOf($translator, ForkTranslator::class);
    }
}
