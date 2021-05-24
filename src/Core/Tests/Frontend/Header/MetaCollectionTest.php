<?php

namespace ForkCMS\Core\Tests\Frontend\Header;

use ForkCMS\Core\Frontend\Domain\Header\MetaCollection;
use ForkCMS\Core\Frontend\Domain\Header\MetaData;
use ForkCMS\Core\Frontend\Domain\Header\MetaLink;
use PHPUnit\Framework\TestCase;

class MetaCollectionTest extends TestCase
{
    public function testStringRepresentation(): void
    {
        $metaCollection = new MetaCollection();

        $metaCollection->addMetaData(MetaData::forName('description', 'lorem ipsum'));
        $metaCollection->addMetaLink(MetaLink::canonical('http://fork-cms.com'));

        self::assertEquals(
            '<meta content="lorem ipsum" name="description">
<link href="http://fork-cms.com" rel="canonical">',
            (string) $metaCollection
        );
    }
}
