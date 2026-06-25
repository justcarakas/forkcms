<?php

namespace ForkCMS\Modules\Internationalisation\Controller;

use Assert\AssertionFailedException;
use ForkCMS\Core\Domain\Util\Ensure;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\MessageCatalogueInterface;
use Symfony\Component\Translation\TranslatorBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class JsTranslationsController
{
    public function __construct(private TranslatorInterface $translator)
    {
    }

    /**
     * @throws AssertionFailedException
     */
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $currentCatalog = Ensure::isImplementingInterface(
                $this->translator,
                TranslatorBagInterface::class
            )->getCatalogue();
        } catch (AssertionFailedException $e) {
            return new JsonResponse(
                ['error' => 'Fork translator not found'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
        $currentLocale = $request->getLocale();
        $translations = [
            'locale' => $currentLocale,
            'translations' => [
                $currentLocale => $currentCatalog->all(),
            ],
        ];

        $fallbackCatalog = $currentCatalog->getFallbackCatalogue();
        if ($fallbackCatalog instanceof MessageCatalogueInterface) {
            $translations['fallback'] = $fallbackCatalog->getLocale();
            $translations['translations'][$translations['fallback']] = $fallbackCatalog->all();
        }

        return new JsonResponse($translations);
    }
}
