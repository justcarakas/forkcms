<?php

declare(strict_types=1);

namespace ForkCMS\Modules\ContentBlocks\Installer;

use ForkCMS\Modules\ContentBlocks\Backend\Actions\ContentBlockAdd;
use ForkCMS\Modules\ContentBlocks\Backend\Actions\ContentBlockDelete;
use ForkCMS\Modules\ContentBlocks\Backend\Actions\ContentBlockEdit;
use ForkCMS\Modules\ContentBlocks\Backend\Actions\ContentBlockIndex;
use ForkCMS\Modules\ContentBlocks\Backend\Actions\ModuleSettings;
use ForkCMS\Modules\ContentBlocks\Domain\ContentBlock\ContentBlock;
use ForkCMS\Modules\ContentBlocks\Domain\ContentBlock\Revision;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleInstaller;
use ForkCMS\Modules\Internationalisation\Domain\Translation\TranslationKey;

final class ContentBlocksInstaller extends ModuleInstaller
{
    #[\Override]
    public function preInstall(): void
    {
        $this->createTableForEntities(ContentBlock::class, Revision::class);
    }

    #[\Override]
    public function install(): void
    {
        $this->importTranslations(__DIR__ . '/../assets/installer/translations.xml');
        $this->createBackendPages();
        $this->defaultModuleSettings();
    }

    private function createBackendPages(): void
    {
        $modulesNavigationItem = $this->getModulesNavigationItem();

        $this->getOrCreateBackendNavigationItem(
            label: TranslationKey::label('ContentBlocks'),
            slug: ContentBlockIndex::getActionSlug(),
            parent: $modulesNavigationItem,
            selectedFor: [
                ContentBlockAdd::getActionSlug(),
                ContentBlockEdit::getActionSlug(),
                ContentBlockDelete::getActionSlug(),
            ]
        );
        $this->getOrCreateBackendNavigationItem(
            label: TranslationKey::label('ContentBlocks'),
            slug: ModuleSettings::getActionSlug(),
            parent: $this->getModuleSettingsNavigationItem(),
        );
    }

    private function defaultModuleSettings(): void
    {
        $this->setSetting(Revision::SETTING_MAX_REVISIONS_NAME, Revision::SETTING_MAX_REVISIONS_DEFAULT);
    }
}
