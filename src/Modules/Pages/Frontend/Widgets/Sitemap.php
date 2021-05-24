<?php

namespace ForkCMS\Modules\Pages\Frontend\Widgets;

use ForkCMS\Core\Frontend\Helper\Base\Widget as FrontendBaseWidget;

/**
 * This is a widget wherein the sitemap lives
 */
class Sitemap extends FrontendBaseWidget
{
    public function execute(): void
    {
        parent::execute();
        $this->loadTemplate();
    }
}
