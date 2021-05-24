<?php

namespace ForkCMS\Modules\Pages\Domain\Page\Command;

use ForkCMS\Modules\Pages\Domain\Page\Page;
use ForkCMS\Modules\Pages\Domain\Page\PageDataTransferObject;
use ForkCMS\Modules\Pages\Domain\Page\Type;
use ForkCMS\Core\Common\Locale;

final class CreatePage extends PageDataTransferObject
{
    public function __construct(Locale $locale, int $templateId, Page $parent = null, Page $copiedFromPage = null)
    {
        parent::__construct($copiedFromPage, $copiedFromPage === null ? $templateId : null);

        $this->locale = $locale;

        if ($copiedFromPage === null) {
            $this->type = Type::root();
            $this->parentId = 0;
        }

        if ($parent instanceof Page && $parent->isAllowChildren()) {
            $this->parentId = $parent->getId();
            $this->type = Type::page();
        }
    }
}
