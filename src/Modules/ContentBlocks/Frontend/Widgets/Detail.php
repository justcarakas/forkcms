<?php

namespace ForkCMS\Modules\ContentBlocks\Frontend\Widgets;

use ForkCMS\Modules\ContentBlocks\Domain\ContentBlock\ContentBlockRepository;
use ForkCMS\Core\Frontend\Helper\Base\Widget as FrontendBaseWidget;
use ForkCMS\Modules\Locale\Frontend\Domain\Locale\Locale;

/**
 * This is the detail widget.
 */
class Detail extends FrontendBaseWidget
{
    public function execute(): void
    {
        parent::execute();

        $contentBlock = $this->get(ContentBlockRepository::class)->findOneByIdAndLocale(
            (int) $this->data['id'],
            Locale::frontendLanguage()
        );

        if ($contentBlock->isHidden()) {
            return;
        }

        $this->template->assign('widgetContentBlocks', $contentBlock);
    }
}
