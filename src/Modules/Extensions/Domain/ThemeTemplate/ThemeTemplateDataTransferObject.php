<?php

namespace ForkCMS\Modules\Extensions\Domain\ThemeTemplate;

use ForkCMS\Core\Domain\Settings\SettingsBag;
use ForkCMS\Modules\Extensions\Domain\Theme\Theme;
use Symfony\Component\Validator\Constraints as Assert;

abstract class ThemeTemplateDataTransferObject
{
    #[Assert\NotBlank(message: 'err.FieldIsRequired')]
    public ?string $name = null;

    #[Assert\NotBlank(message: 'err.FieldIsRequired')]
    public ?string $path = null;

    #[Assert\NotBlank(message: 'err.FieldIsRequired')]
    public ?SettingsBag $settings = null;

    #[Assert\NotBlank(message: 'err.FieldIsRequired')]
    public ?bool $active = null;

    public ?Theme $theme = null;

    protected ?ThemeTemplate $themeTemplateEntity;

    public function __construct(?ThemeTemplate $themeTemplateEntity = null)
    {
        $this->themeTemplateEntity = $themeTemplateEntity;
        if (!$themeTemplateEntity instanceof ThemeTemplate) {
            $this->settings = new SettingsBag();

            return;
        }

        $this->name = $themeTemplateEntity->getName();
        $this->path = $themeTemplateEntity->getPath();
        $this->settings = $themeTemplateEntity->getSettings();
        $this->active = $themeTemplateEntity->getActive();
        $this->theme = $themeTemplateEntity->getTheme();
    }

    public function isNew(): bool
    {
        return $this->themeTemplateEntity === null;
    }

    public function getEntity(): ThemeTemplate
    {
        return $this->themeTemplateEntity;
    }
}
