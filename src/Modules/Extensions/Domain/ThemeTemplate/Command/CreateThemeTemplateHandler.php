<?php

namespace ForkCMS\Modules\Extensions\Domain\ThemeTemplate\Command;

use ForkCMS\Core\Domain\MessageHandler\CommandHandlerInterface;
use ForkCMS\Modules\Extensions\Domain\ThemeTemplate\Event\ThemeTemplateCreatedEvent;
use ForkCMS\Modules\Extensions\Domain\ThemeTemplate\ThemeTemplate;
use ForkCMS\Modules\Extensions\Domain\ThemeTemplate\ThemeTemplateRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final readonly class CreateThemeTemplateHandler implements CommandHandlerInterface
{
    public function __construct(
        private ThemeTemplateRepository $themeTemplateRepository,
        private EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function __invoke(CreateThemeTemplate $createThemeTemplate): void
    {
        $themeTemplate = ThemeTemplate::fromDataTransferObject($createThemeTemplate);
        $this->themeTemplateRepository->save($themeTemplate);
        $createThemeTemplate->setEntity($themeTemplate);
        $this->eventDispatcher->dispatch(ThemeTemplateCreatedEvent::fromCreateCommand($createThemeTemplate));
    }
}
