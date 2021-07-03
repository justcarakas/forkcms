<?php

namespace ForkCMS\Modules\Internationalisation\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\MessageCatalogueInterface;
use Symfony\Component\Translation\TranslatorBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class JsTranslationsController
{

    public function __construct(private TranslatorInterface $translator)
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        if (!$this->translator instanceof TranslatorBagInterface) {
            return new JsonResponse(
                ['error' => 'Fork translator not found'],
                JsonResponse::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        $currentCatalog = $this->translator->getCatalogue();
        $fallbackCatalog = $currentCatalog->getFallbackCatalogue();
        $currentCatalogData = [
            'locale' => $request->getLocale(),
            'translations' => $currentCatalog->all(),
        ];
        $fallbackCatalogData = null;
        if ($fallbackCatalog instanceof MessageCatalogueInterface) {
            $currentCatalogData['fallback'] = $fallbackCatalog->getLocale();
            $fallbackCatalogData = [
                'locale' => $request->getLocale(),
                'translations' => $fallbackCatalog->all(),
            ];
        }

        $catalogs = [
            $currentCatalogData,
        ];
        if ($fallbackCatalogData !== null) {
            $catalogs[] = $fallbackCatalogData;
        }

        return new JsonResponse($catalogs);
    }
}
