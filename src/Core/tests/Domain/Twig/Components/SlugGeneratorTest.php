<?php

declare(strict_types=1);

namespace ForkCMS\Core\tests\Domain\Twig\Components;

use ForkCMS\Core\Domain\Twig\Components\SlugGenerator;
use ForkCMS\Core\tests\WebTestCase;
use ForkCMS\Modules\Pages\Domain\Revision\RevisionRepository;
use Symfony\UX\LiveComponent\Test\InteractsWithLiveComponents;

final class SlugGeneratorTest extends WebTestCase
{
    use InteractsWithLiveComponents;

    public function testGeneratesASlugFromTheGivenValue(): void
    {
        $component = $this->createLiveComponent('SlugGenerator', [
            'generateSlugCallbackClass' => RevisionRepository::class,
            'generateSlugCallbackMethod' => 'slugify',
        ])->call('generate', ['value' => 'Hello World']);

        self::assertInstanceOf(SlugGenerator::class, $component->component());
        self::assertSame('hello-world', $component->component()->slug);
    }

    public function testUnserializesCallbackParameters(): void
    {
        $component = $this->createLiveComponent('SlugGenerator', [
            'generateSlugCallbackClass' => RevisionRepository::class,
            'generateSlugCallbackMethod' => 'slugify',
            'generateSlugCallbackParameters' => serialize([null]),
        ])->call('generate', ['value' => 'Another Title']);

        self::assertSame('another-title', $component->component()->slug);
    }
}
