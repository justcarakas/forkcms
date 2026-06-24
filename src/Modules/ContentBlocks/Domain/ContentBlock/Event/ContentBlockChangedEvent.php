<?php

namespace ForkCMS\Modules\ContentBlocks\Domain\ContentBlock\Event;

use ForkCMS\Modules\ContentBlocks\Domain\ContentBlock\ContentBlock;
use Symfony\Contracts\EventDispatcher\Event;

final class ContentBlockChangedEvent extends Event
{
    public function __construct(public readonly ContentBlock $contentBlock)
    {
    }
}
