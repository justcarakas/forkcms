<?php

namespace ForkCMS\Modules\Extensions\Domain\Module;

use Doctrine\ORM\EntityManagerInterface;
use ForkCMS\Core\Domain\Doctrine\CreateSchema;
use ForkCMS\Modules\Backend\Domain\NavigationItem\NavigationItemRepository;
use ForkCMS\Modules\Backend\Domain\UserGroup\UserGroupRepository;
use ForkCMS\Modules\Internationalisation\Domain\Importer\Importer;
use ForkCMS\Modules\Internationalisation\Domain\Locale\InstalledLocaleRepository;
use ForkCMS\Modules\Internationalisation\Domain\Translation\TranslationRepository;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class ModuleInstallerServices
{
    public function __construct(
        public CreateSchema $createSchema,
        public ModuleRepository $moduleRepository,
        public NavigationItemRepository $navigationRepository,
        public UserGroupRepository $userGroupRepository,
        public TranslationRepository $translationRepository,
        public InstalledLocaleRepository $installedLocaleRepository,
        public Importer $importer,
        public TokenStorageInterface $tokenStorage,
        public EntityManagerInterface $entityManager,
        public MessageBusInterface $commandBus,
        public ModuleSettings $moduleSettings,
        public TranslatorInterface $translator
    ) {
    }
}
