<?php

namespace ForkCMS\Modules\ContentBlocks\Domain\ContentBlock\Command;

use ForkCMS\Core\Common\ForkCMS\Utility\Module\CopyContentToOtherLocale\CopyModuleContentToOtherLocale;

final class CopyContentBlocksToOtherLocale extends CopyModuleContentToOtherLocale
{
    public function getModuleName(): string
    {
        return 'ContentBlocks';
    }
}
