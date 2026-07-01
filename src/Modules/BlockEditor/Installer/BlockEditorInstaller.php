<?php

declare(strict_types=1);

namespace ForkCMS\Modules\BlockEditor\Installer;

use ForkCMS\Core\Domain\Form\EditorType;
use ForkCMS\Modules\BlockEditor\Domain\Editor\BlockEditorType;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleInstaller;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleName;

final class BlockEditorInstaller extends ModuleInstaller
{
    public const bool IS_REQUIRED = true;

    public const bool IS_VISIBLE_IN_OVERVIEW = false;

    #[\Override]
    public function install(): void
    {
        $this->importTranslations(__DIR__ . '/../assets/installer/translations.xml');
        $this->setSetting(EditorType::SETTING_NAME, BlockEditorType::class, ModuleName::core());
    }
}
