<?php

namespace ForkCMS\Modules\Frontend\Frontend\RSS;

use ForkCMS\Modules\Frontend\Domain\RSSAction\AbstractRSSActionController;
use ForkCMS\Modules\Internationalisation\Domain\Translation\TranslationKey;
use Laminas\Feed\Writer\Writer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * This class will handle the frontend RSS 404 page.
 */
final class NotFound extends AbstractRSSActionController
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function getResponse(Request $request): Response
    {
        return new Response(
            $this->getFeed()->export(Writer::TYPE_RSS_ANY),
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
        return $this->translator->trans(TranslationKey::error('404'));
    }

    protected function feedDescription(Request $request): string
    {
        return $this->translator->trans(TranslationKey::error('404RssDescription'));
    }

    protected function feedLink(Request $request): string
    {
        return $request->getSchemeAndHttpHost() . $request->getBaseUrl();
    }
}
