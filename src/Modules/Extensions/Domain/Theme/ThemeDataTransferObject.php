<?php

namespace ForkCMS\Modules\Extensions\Domain\Theme;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use ForkCMS\Core\Domain\Settings\SettingsBag;
use ForkCMS\Modules\Extensions\Domain\ThemeTemplate\ThemeTemplate;
use Symfony\Component\Validator\Constraints as Assert;

abstract class ThemeDataTransferObject
{
    #[Assert\NotBlank(message: 'err.FieldIsRequired')]
    public ?string $name = null;

    #[Assert\NotBlank(message: 'err.FieldIsRequired')]
    public ?string $description = null;

    #[Assert\NotBlank(message: 'err.FieldIsRequired')]
    public ?bool $active = null;

    #[Assert\NotBlank(message: 'err.FieldIsRequired')]
    public ?Collection $templates = null;

    #[Assert\NotBlank(message: 'err.FieldIsRequired')]
    public ?SettingsBag $settings = null;

    #[Assert\NotBlank(message: 'err.FieldIsRequired')]
    public ?ThemeTemplate $defaultTemplate = null;

    protected ?Theme $themeEntity;

    protected function __construct(?Theme $themeEntity = null)
    {
        $this->themeEntity = $themeEntity;
        if (!$themeEntity instanceof Theme) {
            $this->settings = new SettingsBag();
            $this->templates = new ArrayCollection();

            return;
        }

        $this->name = $themeEntity->getName();
        $this->description = $themeEntity->getDescription();
        $this->active = $themeEntity->getActive();
        $this->templates = $themeEntity->getTemplates();
        $this->settings = $themeEntity->getSettings();
        if (!$themeEntity->getTemplates()->isEmpty()) {
            $this->defaultTemplate = $themeEntity->getDefaultTemplate();
        }
    }

    public function isNew(): bool
    {
        return $this->themeEntity === null;
    }

    public function getEntity(): Theme
    {
        return $this->themeEntity;
    }
}
