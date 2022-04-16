<?php

namespace ForkCMS\Modules\Internationalisation\Backend\Ajax;

use ForkCMS\Modules\Backend\Domain\AjaxAction\AbstractAjaxActionController;
use ForkCMS\Modules\Internationalisation\Domain\Translation\TranslationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Edit a translation over ajax
 */
final class TranslationEdit extends AbstractAjaxActionController
{
    public function __construct(private readonly TranslationRepository $translationRepository)
    {
    }

    protected function execute(Request $request): void
    {
        $translation = $this->translationRepository->find($request->query->get('id'));
        if ($translation === null) {
            throw new NotFoundHttpException('No translation found with id ' . $request->query->get('id'));
        }

        $translation->change($request->request->get('content'));
        $this->translationRepository->save($translation);
    }
}
