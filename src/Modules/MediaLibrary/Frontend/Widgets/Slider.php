<?php

namespace ForkCMS\Modules\MediaLibrary\Frontend\Widgets;

use ForkCMS\Modules\MediaLibrary\Frontend\Widgets\Base\FrontendMediaWidget;

/**
 * This will show a MediaGroup (Custom Module) or a MediaGallery (Media Module) in a slider using BxSlider.
 */
class Slider extends FrontendMediaWidget
{
    public function execute(): void
    {
        $this->loadData();

        // We need to have a MediaGroup to show this widget
        if (!$this->mediaGroup) {
            return;
        }

        parent::execute();
        $this->loadTemplate();
        $this->parse();
    }
}
