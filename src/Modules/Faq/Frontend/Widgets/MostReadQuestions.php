<?php

namespace ForkCMS\Modules\Faq\Frontend\Widgets;

use ForkCMS\Core\Common\ModulesSettings;
use ForkCMS\Core\Frontend\Helper\Base\Widget as FrontendBaseWidget;
use ForkCMS\Modules\Faq\Frontend\Helper\Model as FrontendFaqModel;

/**
 * This is a widget with most read faq-questions
 */
class MostReadQuestions extends FrontendBaseWidget
{
    public function execute(): void
    {
        parent::execute();

        $this->loadTemplate();
        $this->parse();
    }

    private function parse(): void
    {
        $this->template->assign(
            'widgetFaqMostRead',
            FrontendFaqModel::getMostRead(
                $this->get(ModulesSettings::class)->get($this->getModule(), 'most_read_num_items', 10)
            )
        );
    }
}
