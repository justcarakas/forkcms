<?php

declare(strict_types=1);

namespace ForkCMS\Modules\Frontend\Installer;

use ForkCMS\Modules\Extensions\Domain\Module\ModuleInstaller;
use ForkCMS\Modules\Frontend\Backend\Actions\ModuleSettings;
use ForkCMS\Modules\Frontend\Domain\Block\Block;
use ForkCMS\Modules\Frontend\Domain\Meta\Meta;
use ForkCMS\Modules\Internationalisation\Domain\Translation\TranslationKey;

final class FrontendInstaller extends ModuleInstaller
{
    public const bool IS_REQUIRED = true;

    #[\Override]
    public function preInstall(): void
    {
        $this->createTableForEntities(Meta::class, Block::class);
    }

    #[\Override]
    public function install(): void
    {
        $this->importTranslations(__DIR__ . '/../assets/installer/translations.xml');
        $this->defaultModuleSettings();
        $this->createBackendPages();
    }

    private function createBackendPages(): void
    {
        $this->getOrCreateBackendNavigationItem(
            TranslationKey::label('Frontend'),
            ModuleSettings::getActionSlug(),
            $this->getModuleSettingsNavigationItem()
        );
    }

    private function defaultModuleSettings(): void
    {
        $this->setSetting('site_html_head', '');
        $this->setSetting('site_html_start_of_body', '');
        $this->setSetting('site_html_end_of_body', '');
        $this->setSetting('consent_dialog_levels', []);
        $this->setSetting('google_analytics_enabled', false);
        $this->setSetting('google_analytics_value', null);
        $this->setSetting('google_tag_manager_enabled', false);
        $this->setSetting('google_tag_manager_value', null);
    }
}
