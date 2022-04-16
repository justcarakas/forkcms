<?php

namespace ForkCMS\Modules\Internationalisation\Backend\Ajax;

use ForkCMS\Modules\Backend\Domain\AjaxAction\AbstractAjaxActionController;
use ForkCMS\Modules\Internationalisation\Domain\Translation\Command\ChangeTranslation;
use ForkCMS\Modules\Internationalisation\Domain\Translation\TranslationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Edit a translation over ajax
 */
final class TranslationEdit extends AbstractAjaxActionController
{
    public function __construct(
        private readonly TranslationRepository $translationRepository,
        private readonly MessageBusInterface $commandBus,
    ) {
    }

    protected function execute(Request $request): void
    {
        $translation = $this->translationRepository->find($request->query->get('id'));
        if ($translation === null) {
            throw new NotFoundHttpException('No translation found with id ' . $request->query->get('id'));
        }

        $editTranslation = new ChangeTranslation($translation);
        $editTranslation->value = $request->request->get('content');
        $this->commandBus->dispatch($editTranslation);
    }
}
