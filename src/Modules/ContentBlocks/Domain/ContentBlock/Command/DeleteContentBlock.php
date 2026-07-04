<?php

declare(strict_types=1);

namespace ForkCMS\Modules\ContentBlocks\Domain\ContentBlock\Command;

final readonly class DeleteContentBlock
{
    public function __construct(public int $id)
    {
    }
}
