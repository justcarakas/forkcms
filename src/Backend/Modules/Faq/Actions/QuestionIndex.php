<?php

namespace Backend\Modules\Faq\Actions;

use Backend\Core\Engine\Base\ActionIndex;
use Backend\Core\Engine\Model;
use Backend\Core\Language\Locale;
use Backend\Modules\Faq\Domain\Category\CategoryRepository;
use Backend\Modules\Faq\Domain\Question\QuestionDataGrid;
use Symfony\Component\HttpKernel\KernelInterface;

final class QuestionIndex extends ActionIndex
{
    /** @var CategoryRepository */
    private $categoryRepository;

    public function setKernel(KernelInterface $kernel = null): void
    {
        parent::setKernel($kernel);

        $this->categoryRepository = $this->get(CategoryRepository::class);
    }

    public function execute(): void
    {
        parent::execute();

        $workingLocale = Locale::workingLocale();
        $categories = $this->categoryRepository->findByLocale($workingLocale);

        if (count($categories) === 0) {
            $this->redirect(Model::createUrlForAction('CategoryAdd'));
        }

        $dataGrids = [];
        foreach ($categories as $category) {
            $dataGrids[] = [
                'id' => $category->getId(),
                'name' => $category->getTranslation($workingLocale)->getName(),
                'dataGrid' => QuestionDataGrid::getHtml($workingLocale, $category),
            ];
        }

        $this->template->assign('dataGrids', $dataGrids);

        $this->parse();
        $this->display();
    }
}
