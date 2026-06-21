<?php

namespace ForkCMS\Modules\Frontend\Frontend\RSS;

use ForkCMS\Modules\Frontend\Domain\RSSAction\AbstractRSSActionController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * This class will handle the frontend RSS 404 page.
 */
final class NotFound extends AbstractRSSActionController
{
    public function __construct()
    {
    }

    protected function execute(Request $request): void
    {
        //$this->assign('message', TranslationKey::error('NotFound')->trans($this->translator));
    }

    public function getResponse(Request $request): Response
    {
        $response = parent::getResponse($request);
        $response->setStatusCode(Response::HTTP_NOT_FOUND);

        return $response;
    }

    protected function getEntries(Request $request): iterable
    {
        // TODO: Implement getEntries() method.
        throw new \Exception('Method not implemented');
    }

    protected function feedTitle(Request $request): string
    {
        // TODO: Implement feedTitle() method.
        throw new \Exception('Method not implemented');
    }

    protected function feedDescription(Request $request): string
    {
        // TODO: Implement feedDescription() method.
        throw new \Exception('Method not implemented');
    }

    protected function feedLink(Request $request): string
    {
        // TODO: Implement feedLink() method.
        throw new \Exception('Method not implemented');
    }
}
