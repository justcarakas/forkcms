<?php

namespace ForkCMS\Modules\Frontend\Domain\RSSAction;

use Laminas\Feed\Writer\Entry;
use Laminas\Feed\Writer\Feed;
use Laminas\Feed\Writer\Writer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractRSSActionController implements RSSActionControllerInterface
{
    private(set) Feed $feed;

    #[\Override]
    final public static function getRSSActionSlug(): RSSActionSlug
    {
        return RSSActionSlug::fromFQCN(static::class);
    }

    #[\Override]
    public function __invoke(Request $request): Response
    {
        $this->feed = new Feed();
        $this->execute($request);

        return $this->getResponse($request);
    }

    protected function execute(Request $request): void
    {
        $this->feed->setTitle($this->feedTitle($request));
        $this->feed->setDescription($this->feedDescription($request));
        $this->feed->setLink($this->feedLink($request));
        $this->feed->setLanguage($request->getLocale());
        $this->feed->setGenerator('Fork CMS', uri: 'https://www.fork-cms.com');

        foreach ($this->getEntries($request) as $entry) {
            // TODO check if dates have been set to set the feed's date
            $this->feed->addEntry($entry);
        }
    }

    public function getResponse(Request $request): Response
    {
        return new Response(
            $this->feed->export(Writer::TYPE_RSS_ANY),
            headers: ['Content-Type' => 'application/rss+xml; charset=utf-8']
        );
    }

    /** @return iterable<Entry> */
    abstract protected function getEntries(Request $request): iterable;

    /**
     * The name of the channel.
     * It's how people refer to your service.
     * If you have an HTML website that contains the same information as your RSS file,
     * the title of your channel should be the same as the title of your website.
     *
     * @see https://www.rssboard.org/rss-specification
     */
    abstract protected function feedTitle(Request $request): string;

    /**
     * Phrase or sentence describing the channel.
     *
     * @see https://www.rssboard.org/rss-specification
     */
    abstract protected function feedDescription(Request $request): string;

    /**
     * The URL to the website corresponding to the channel.
     *
     * @see https://www.rssboard.org/rss-specification
     */
    abstract protected function feedLink(Request $request): string;
}
