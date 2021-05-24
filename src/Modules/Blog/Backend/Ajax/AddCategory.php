<?php

namespace ForkCMS\Modules\Blog\Backend\Ajax;

use ForkCMS\Core\Backend\Domain\Ajax\AjaxAction as BackendBaseAJAXAction;
use ForkCMS\Modules\Locale\Backend\Domain\Translator\Language as BL;
use ForkCMS\Modules\Blog\Backend\Helper\Model as BackendBlogModel;
use Symfony\Component\HttpFoundation\Response;

/**
 * This add-action will create a new category using Ajax
 */
class AddCategory extends BackendBaseAJAXAction
{
    public function execute(): void
    {
        parent::execute();

        // get parameters
        $categoryTitle = trim($this->getRequest()->request->get('value', ''));

        // validate
        if ($categoryTitle === '') {
            $this->output(Response::HTTP_BAD_REQUEST, null, BL::err('TitleIsRequired'));

            return;
        }

        // get the data
        // build array
        $item = [
            'title' => \SpoonFilter::htmlspecialchars($categoryTitle),
            'language' => BL::getWorkingLanguage(),
        ];

        $meta = [
            'keywords' => $item['title'],
            'keywords_overwrite' => false,
            'description' => $item['title'],
            'description_overwrite' => false,
            'title' => $item['title'],
            'title_overwrite' => false,
            'url' => BackendBlogModel::getUrlForCategory(\SpoonFilter::urlise($item['title'])),
        ];

        // update
        $item['id'] = BackendBlogModel::insertCategory($item, $meta);

        // output
        $this->output(Response::HTTP_OK, $item, vsprintf(BL::msg('AddedCategory'), [$item['title']]));
    }
}
