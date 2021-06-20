<?php

namespace ForkCMS\Modules\Faq\Frontend\Actions;

use ForkCMS\Modules\Extensions\Domain\ModuleSetting\ModuleSettingRepository;
use ForkCMS\Core\Frontend\Helper\Base\Block as FrontendBaseBlock;
use ForkCMS\Modules\Faq\Frontend\Helper\Model as FrontendFaqModel;

/**
 * This is the index-action
 */
class Index extends FrontendBaseBlock
{
    /**
     * @var array
     */
    private $categories = [];

    public function execute(): void
    {
        parent::execute();

        $this->getData();
        $this->loadTemplate();
        $this->parse();
    }

    private function getData(): void
    {
        $limit = $this->get(ModuleSettingRepository::class)->get($this->getModule(), 'overview_num_items_per_category', 10);

        $categoriesWithQuestions = array_map(
            function (array $category) use ($limit) {
                $category['questions'] = FrontendFaqModel::getAllForCategory($category['id'], $limit);

                return $category;
            },
            FrontendFaqModel::getCategories()
        );

        $this->categories = array_filter(
            $categoriesWithQuestions,
            function (array $category) {
                return !empty($category['questions']);
            }
        );
    }

    private function parse(): void
    {
        $this->template->assign('faqCategories', $this->categories);
        $this->template->assign(
            'allowMultipleCategories',
            $this->get(ModuleSettingRepository::class)->get($this->getModule(), 'allow_multiple_categories', true)
        );
    }
}
