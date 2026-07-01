<?php

declare(strict_types=1);

namespace ForkCMS\Modules\Frontend\Domain\Block\Event;

use ForkCMS\Modules\Frontend\Domain\Block\Block;
use Symfony\Contracts\EventDispatcher\Event;

final class IsBlockInUseEvent extends Event
{
    public function __construct(
        public readonly Block $block,
        private(set) bool $inUse = false
    ) {
    }

    public function registerUsage(): void
    {
        $this->inUse = true;
        $this->stopPropagation();
    }
}
