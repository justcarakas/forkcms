<?php

namespace ForkCMS\Modules\ContentBlocks\Domain\ContentBlock\Command;

final class DeleteContentBlock
{
    public function __construct(public readonly int $id)
    {
    }
}
