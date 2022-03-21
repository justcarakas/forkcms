<?php

namespace ForkCMS\Modules\Extensions\Domain\ThemeTemplate;

use Assert\Assertion;
use ForkCMS\Core\Domain\Settings\SettingsBag;
use ForkCMS\Modules\Extensions\Domain\Theme\Theme;
use ForkCMS\Modules\Internationalisation\Domain\Translation\TranslationKey;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContext;

#[Assert\Callback(callback: 'validateThemeTemplate')]
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

    public bool $default = false;

    public ?string $layout = null;

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
        $this->default = $themeTemplateEntity->isDefault();
        $this->layout = $themeTemplateEntity->getSetting('layout');
    }

    public function isNew(): bool
    {
        return $this->themeTemplateEntity === null;
    }

    public function getEntity(): ThemeTemplate
    {
        return $this->themeTemplateEntity;
    }

    /** @return string[] */
    public static function getPositionsFromFormat(string $format): array
    {
        return array_unique(
            array_filter(
                array_map(
                    static fn ($position): string => preg_replace('/[^a-zA-Z0-9]+/', '', $position),
                    explode(',', $format)
                ),
                strlen(...)
            )
        );
    }
}
