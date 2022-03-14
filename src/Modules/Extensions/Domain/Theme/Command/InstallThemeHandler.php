<?php

namespace ForkCMS\Modules\Extensions\Domain\Theme\Command;

use ForkCMS\Core\Domain\MessageHandler\CommandHandlerInterface;
use ForkCMS\Modules\Extensions\Domain\Theme\Event\ThemeInstalledEvent;
use ForkCMS\Modules\Extensions\Domain\Theme\Theme;
use ForkCMS\Modules\Extensions\Domain\Theme\ThemeRepository;
use ForkCMS\Modules\Extensions\Domain\ThemeTemplate\Event\ThemeTemplateCreatedEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class InstallThemeHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly ThemeRepository $themeRepository,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function __invoke(InstallTheme $installTheme)
    {
        $theme = Theme::fromDataTransferObject($installTheme->theme);
        $installTheme->theme->setTheme($theme);
        $this->themeRepository->save($theme);

        $this->eventDispatcher->dispatch(new ThemeInstalledEvent($theme));
        foreach ($theme->getTemplates() as $themeTemplate) {
            $this->eventDispatcher->dispatch(new ThemeTemplateCreatedEvent($themeTemplate));
        }
    }
}
