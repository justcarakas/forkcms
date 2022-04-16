<?php

namespace ForkCMS\Modules\Internationalisation\Domain\Translation\Command;

use ForkCMS\Core\Domain\MessageHandler\CommandHandlerInterface;
use ForkCMS\Modules\Internationalisation\Domain\Translation\TranslationRepository;

final class DeleteTranslationHandler implements CommandHandlerInterface
{
    public function __construct(private TranslationRepository $translationRepository)
    {
    }

    public function __invoke(DeleteTranslation $deleteTranslation): void
    {
        $this->translationRepository->remove($this->translationRepository->find($deleteTranslation->getTranslationId()));
    }
}
