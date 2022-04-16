<?php

namespace ForkCMS\Modules\Internationalisation\Domain\Translation\Command;

use ForkCMS\Core\Domain\MessageHandler\CommandHandlerInterface;
use ForkCMS\Modules\Internationalisation\Domain\Translation\Translation;
use ForkCMS\Modules\Internationalisation\Domain\Translation\TranslationRepository;

final class ChangeTranslationHandler implements CommandHandlerInterface
{
    public function __construct(private readonly TranslationRepository $translationRepository)
    {
    }

    public function __invoke(ChangeTranslation $changeTranslation): void
    {
        $this->translationRepository->save(Translation::fromDataTransferObject($changeTranslation));
    }
}
