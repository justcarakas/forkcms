<?php

namespace ForkCMS\Modules\Frontend\Installer;

use ForkCMS\Core\Domain\Settings\SettingsBag;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleInstaller;
use ForkCMS\Modules\Frontend\Domain\Action\ActionName;
use ForkCMS\Modules\Frontend\Domain\Block\Block;
use ForkCMS\Modules\Frontend\Domain\Meta\Meta;
use ForkCMS\Modules\Frontend\Domain\Widget\WidgetName;

final class FrontendInstaller extends ModuleInstaller
{
    public const IS_REQUIRED = true;

    public function preInstall(): void
    {
        $this->createTableForEntities(Meta::class, Block::class);
    }

    public function install(): void
    {
        $this->importTranslations(__DIR__ . '/../assets/installer/translations.xml');

        // @TODO remove this after testing
        $this->getOrCreateFrontendBlock(ActionName::fromString('Test'));
        $this->getOrCreateFrontendBlock(ActionName::fromString('Bob'));
        $this->getOrCreateFrontendBlock(WidgetName::fromString('Test'), null, new SettingsBag(['extra_label' => 'jef']));
        $this->getOrCreateFrontendBlock(WidgetName::fromString('Test'));
        $this->getOrCreateFrontendBlock(WidgetName::fromString('Test'), null, new SettingsBag(['extra_label' => 'bob']));
        $this->getOrCreateFrontendBlock(WidgetName::fromString('Test'), null, new SettingsBag(['extra_label' => 'bob %1$s', 'extra_label_parameters' => [1]]));
    }
}
