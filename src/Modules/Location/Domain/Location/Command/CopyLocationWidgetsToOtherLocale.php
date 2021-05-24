<?php

namespace ForkCMS\Modules\Location\Domain\Location\Command;

use ForkCMS\Core\Common\ForkCMS\Utility\Module\CopyContentToOtherLocale\CopyModuleContentToOtherLocale;

final class CopyLocationWidgetsToOtherLocale extends CopyModuleContentToOtherLocale
{
    public function getModuleName(): string
    {
        return 'Location';
    }
}
