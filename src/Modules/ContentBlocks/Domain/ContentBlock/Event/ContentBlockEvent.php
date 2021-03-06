<?php

namespace ForkCMS\Modules\ContentBlocks\Domain\ContentBlock\Event;

use ForkCMS\Modules\ContentBlocks\Domain\ContentBlock\ContentBlock;
use Symfony\Contracts\EventDispatcher\Event;

abstract class ContentBlockEvent extends Event
{
    /** @var ContentBlock */
    private $contentBlock;

    public function __construct(ContentBlock $contentBlock)
    {
        $this->contentBlock = $contentBlock;
    }

    public function getContentBlock(): ContentBlock
    {
        return $this->contentBlock;
    }
}
