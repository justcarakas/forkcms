<?php

namespace ForkCMS\Modules\Faq\Frontend\Widgets;

use ForkCMS\Modules\Extensions\Domain\ModuleSetting\ModuleSettingRepository;
use ForkCMS\Core\Frontend\Helper\Base\Widget as FrontendBaseWidget;
use ForkCMS\Modules\Faq\Frontend\Helper\Model as FrontendFaqModel;

/**
 * This is a widget with most read faq-questions
 */
class CategoryList extends FrontendBaseWidget
{
    public function execute(): void
    {
        parent::execute();

        $this->loadTemplate();
        $this->parse();
    }

    private function parse(): void
    {
        $this->template->assign('widgetFaqCategory', FrontendFaqModel::getCategoryById($this->data['id']));
        $this->template->assign(
            'widgetFaqCategoryList',
            FrontendFaqModel::getAllForCategory(
                $this->data['id'],
                $this->get(ModuleSettingRepository::class)->get($this->getModule(), 'most_read_num_items', 10)
            )
        );
    }
}
