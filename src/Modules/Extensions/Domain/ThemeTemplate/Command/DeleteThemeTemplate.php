<?php

declare(strict_types=1);

namespace ForkCMS\Modules\Extensions\Domain\ThemeTemplate\Command;

use ForkCMS\Modules\Extensions\Domain\ThemeTemplate\ThemeTemplate;

final class DeleteThemeTemplate
{
    private ThemeTemplate $themeTemplateEntity;

    public function __construct(public readonly int $id)
    {
    }

    public function getEntity(): ThemeTemplate
    {
        return $this->themeTemplateEntity;
    }

    public function setEntity(ThemeTemplate $themeTemplate): void
    {
        $this->themeTemplateEntity = $themeTemplate;
    }
}
