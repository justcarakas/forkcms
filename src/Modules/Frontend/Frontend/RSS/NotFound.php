<?php

declare(strict_types=1);

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

    #[\Override]
    public function getResponse(Request $request): Response
    {
        return new Response(
            $this->feed->export(Writer::TYPE_RSS_ANY),
            Response::HTTP_NOT_FOUND,
            ['Content-Type' => 'application/rss+xml; charset=utf-8']
        );
    }

    #[\Override]
    protected function getEntries(Request $request): iterable
    {
        return [];
    }

    #[\Override]
    protected function feedTitle(Request $request): string
    {
        return TranslationKey::error('404')->trans($this->translator);
    }

    #[\Override]
    protected function feedDescription(Request $request): string
    {
        return TranslationKey::error('404RssDescription')->trans($this->translator);
    }

    #[\Override]
    protected function feedLink(Request $request): string
    {
        return $request->getSchemeAndHttpHost() . $request->getBaseUrl();
    }
}
