<?php

declare(strict_types=1);

namespace ForkCMS\Modules\ContentBlocks\Domain\ContentBlock\Event;

use ForkCMS\Modules\ContentBlocks\Domain\ContentBlock\Revision;
use Symfony\Contracts\EventDispatcher\Event;

final class ContentBlockRevisionCreatedEvent extends Event
{
    public function __construct(public readonly Revision $contentBlockRevision)
    {
    }
}
