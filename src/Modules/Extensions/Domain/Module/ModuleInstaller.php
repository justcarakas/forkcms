<?php

namespace ForkCMS\Modules\Extensions\Domain\Module;

use ForkCMS\Core\Domain\Doctrine\CreateSchema;
use ForkCMS\Modules\Backend\Domain\Action\ActionSlug;
use ForkCMS\Modules\Backend\Domain\Action\ModuleAction;
use ForkCMS\Modules\Backend\Domain\NavigationItem\NavigationItem;
use ForkCMS\Modules\Backend\Domain\NavigationItem\NavigationItemRepository;
use ForkCMS\Modules\Backend\Domain\UserGroup\UserGroup;
use ForkCMS\Modules\Backend\Domain\UserGroup\UserGroupRepository;
use ForkCMS\Modules\Backend\Domain\Widget\ModuleWidget;
use ForkCMS\Modules\Backend\Installer\BackendInstaller;
use ForkCMS\Modules\Extensions\Domain\ModuleSetting\ModuleSettingRepository;
use ForkCMS\Modules\Extensions\Installer\ExtensionsInstaller;
use ForkCMS\Modules\Internationalisation\Domain\Importer\Importer;
use ForkCMS\Modules\Internationalisation\Domain\Locale\InstalledLocaleRepository;
use ForkCMS\Modules\Internationalisation\Domain\Translation\TranslationKey;
use ForkCMS\Modules\Internationalisation\Domain\Translation\TranslationRepository;
use ForkCMS\Modules\Internationalisation\Installer\InternationalisationInstaller;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\StampInterface;

abstract class ModuleInstaller
{
    public const IS_REQUIRED = false;

    /** If the module should show up on a list of installed or installable modules */
    public const IS_VISIBLE_IN_OVERVIEW = true;

    /** @var array<string,ModuleName> */
    private array $moduleDependencies = [];

    /** @var array<string,ModuleName> */
    private ?array $defaultModuleDependencies = null;

    public function __construct(
        protected CreateSchema $createSchema,
        protected ModuleRepository $moduleRepository,
        protected NavigationItemRepository $navigationRepository,
        protected UserGroupRepository $userGroupRepository,
        protected ModuleSettingRepository $moduleSettingRepository,
        protected TranslationRepository $translationRepository,
        protected InstalledLocaleRepository $installedLocaleRepository,
        protected Importer $importer,
        protected SessionInterface $session,
        private MessageBusInterface $commandBus,
        private MessageBusInterface $eventBus,
    ) {
    }

    final public static function getModuleName(): ModuleName
    {
        return ModuleName::fromFQCN(static::class);
    }

    /**
     * Use this method to perform the actions needed to install the module
     */
    abstract public function install(): void;

    /**
     * Use this method to perform actions before the uninstalled module dependencies are installed
     */
    public function preInstall(): void
    {
    }

    final public function registerModule(): void
    {
        $this->moduleRepository->save(Module::fromModuleName(static::getModuleName()));
    }

    final protected function addModuleDependency(ModuleName $moduleName): void
    {
        $this->moduleDependencies[$moduleName->getName()] = $moduleName;
    }

    /** @return array<string,ModuleName> */
    final public function getModuleDependencies(): array
    {
        return array_merge($this->moduleDependencies, $this->getDefaultModuleDependencies());
    }

    final public function createDatabasesForEntities(string ...$entityClasses): void
    {
        $this->createSchema->forEntityClasses(...$entityClasses);
    }

    /** @return array<string,ModuleName> */
    private function getDefaultModuleDependencies(): array
    {
        if ($this->defaultModuleDependencies !== null) {
            return $this->defaultModuleDependencies;
        }
        $this->defaultModuleDependencies = [];

        $backendInstaller = BackendInstaller::getModuleName();
        if ($backendInstaller === static::getModuleName()) {
            return $this->defaultModuleDependencies;
        }
        $this->defaultModuleDependencies[$backendInstaller->getName()] = $backendInstaller;

        $internationalisationInstaller = InternationalisationInstaller::getModuleName();
        if ($internationalisationInstaller === static::getModuleName()) {
            return $this->defaultModuleDependencies;
        }
        $this->defaultModuleDependencies[$internationalisationInstaller->getName()] = $internationalisationInstaller;

        $extensionInstaller = ExtensionsInstaller::getModuleName();
        if ($extensionInstaller === static::getModuleName()) {
            return $this->defaultModuleDependencies;
        }
        $this->defaultModuleDependencies[$extensionInstaller->getName()] = $extensionInstaller;

        return $this->defaultModuleDependencies;
    }

    /** @param array<ActionSlug> $selectedFor */
    final protected function getOrCreateBackendNavigationItem(
        TranslationKey $label,
        ?ActionSlug $slug = null,
        ?NavigationItem $parent = null,
        array $selectedFor = [],
        ?int $sequence = null,
        bool $visibleInNavigationMenu = true,
    ): NavigationItem {
        $navigationItem = $this->navigationRepository->findUnique(
            $label,
            $slug,
            $parent
        );
        if (!$navigationItem instanceof NavigationItem) {
            $navigationItem = new NavigationItem($label, $slug, $parent, $visibleInNavigationMenu, $sequence);
            $this->navigationRepository->save($navigationItem);
        }

        foreach ($selectedFor as $selectedForLabel => $selectedForSlug) {
            $this->getOrCreateBackendNavigationItem(
                TranslationKey::label($selectedForLabel),
                $selectedForSlug,
                $navigationItem,
                [],
                null,
                false
            );
        }

        if ($slug instanceof ActionSlug) {
            $this->allowGroupToAccessModuleAction($slug->asModuleAction());
        }

        return $navigationItem;
    }

    final protected function getModulesNavigationItem(): NavigationItem
    {
        return $this->getOrCreateBackendNavigationItem(
            TranslationKey::label('Modules'),
            null,
            null,
            [],
            4,
        );
    }

    final protected function getSettingsNavigationItem(): NavigationItem
    {
        return $this->getOrCreateBackendNavigationItem(
            TranslationKey::label('Settings'),
            null,
            null,
            [],
            999,
        );
    }

    final protected function getModuleSettingsNavigationItem(): NavigationItem
    {
        return $this->getOrCreateBackendNavigationItem(
            TranslationKey::label('Modules'),
            null,
            $this->getSettingsNavigationItem(),
        );
    }

    /**
     * @param UserGroup|null $userGroup Defaults to the admin user group
     */
    final protected function allowGroupToAccessModuleAction(
        ModuleAction $moduleAction,
        UserGroup $userGroup = null
    ): void {
        $userGroup = $userGroup ?? $this->userGroupRepository->getAdminUserGroup();
        $userGroup->addModule($moduleAction->getModule());
        $userGroup->addAction($moduleAction);
    }

    /**
     * @param UserGroup|null $userGroup Defaults to the admin user group
     */
    final protected function allowGroupToAccessModuleWidget(
        ModuleWidget $moduleWidget,
        UserGroup $userGroup = null
    ): void {
        $userGroup = $userGroup ?? $this->userGroupRepository->getAdminUserGroup();
        $userGroup->addModule($moduleWidget->getModule());
        $userGroup->addWidget($moduleWidget);
    }

    final protected function setSetting(string $key, mixed $value, ModuleName $moduleName = null): void
    {
        $this->moduleSettingRepository->set($moduleName ?? self::getModuleName(), $key, $value);
    }

    final protected function importTranslations(
        string $translationPath,
        bool $overwriteConflicts = false
    ): void {
        $this->importer->import($translationPath, $overwriteConflicts);
    }

    /** @param StampInterface[] $stamps */
    public function dispatchCommand(object $command, array $stamps = []): Envelope
    {
        return $this->commandBus->dispatch($command, $stamps);
    }

    /** @param StampInterface[] $stamps */
    public function dispatchEvent(object $event, array $stamps = []): Envelope
    {
        return $this->eventBus->dispatch($event, $stamps);
    }
}
