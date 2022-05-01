<?php

namespace ForkCMS\Modules\Pages\Domain\Revision\Command;

use ForkCMS\Modules\Internationalisation\Domain\Locale\Locale;
use ForkCMS\Modules\Pages\Domain\Page\Page;
use ForkCMS\Modules\Pages\Domain\Revision\Revision;
use ForkCMS\Modules\Pages\Domain\Revision\RevisionDataTransferObject;

final class CreateRevision extends RevisionDataTransferObject
{
    public function __construct(Page $page, Locale $locale, public readonly bool $clearCache = true)
    {
        parent::__construct();
        $this->page = $page;
        $this->locale = $locale;
    }

    public function setEntity(Revision $revision): void
    {
        $this->revisionEntity = $revision;
    }
}
