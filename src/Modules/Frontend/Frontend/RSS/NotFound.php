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
    }

    public function getResponse(Request $request): Response
    {
        return new Response(
            '',
            Response::HTTP_NOT_FOUND,
            ['Content-Type' => 'application/rss+xml; charset=utf-8']
        );
    }

    protected function getEntries(Request $request): iterable
    {
        return [];
    }

    protected function feedTitle(Request $request): string
    {
        return '';
    }

    protected function feedDescription(Request $request): string
    {
        return '';
    }

    protected function feedLink(Request $request): string
    {
        return '';
    }
}
