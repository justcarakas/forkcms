<?php

namespace ForkCMS\Modules\Pages\Domain\Page;

use ForkCMS\Modules\Internationalisation\Backend\Domain\Locale\Locale;

final class CopyPageDataTransferObject
{
    /** @var Locale */
    public $from;

    /** @var Locale */
    public $to;

    /** @var Page|null */
    public $pageToCopy;

    public function __construct(Locale $from = null, Page $pageToCopy = null)
    {
        $this->from = $from;
        $this->pageToCopy = $pageToCopy;
    }
}
