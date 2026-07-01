<?php

declare(strict_types=1);

namespace ForkCMS\Modules\Extensions\Domain\Module\Command;

use ForkCMS\Core\Domain\MessageHandler\CommandHandlerInterface;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleRepository;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleSettings;

final readonly class ChangeModuleSettingsHandler implements CommandHandlerInterface
{
    public function __construct(
        private ModuleRepository $moduleRepository,
        private ModuleSettings $moduleSettings,
    ) {
    }

    public function __invoke(ChangeModuleSettings $changeModuleSettings): void
    {
        $this->moduleRepository->save($changeModuleSettings->module);
        $this->moduleSettings->invalidateCache();
    }
}
