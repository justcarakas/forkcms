<?php

namespace ForkCMS\Modules\Faq\Backend\Actions;

use ForkCMS\Core\Backend\Domain\Action\ActionAdd as BackendBaseActionAdd;
use ForkCMS\Modules\Authentication\Backend\Domain\Authentication\Authentication as BackendAuthentication;
use ForkCMS\Core\Backend\Domain\Form\Form as BackendForm;
use ForkCMS\Modules\Internationalisation\Backend\Domain\Translator\Language as BL;
use ForkCMS\Core\Backend\Helper\Model as BackendModel;
use ForkCMS\Core\Backend\Domain\Meta\Meta as BackendMeta;
use ForkCMS\Modules\Faq\Backend\Helper\Model as BackendFaqModel;
use ForkCMS\Modules\Pages\Domain\Page\Page;
use ForkCMS\Modules\Search\Backend\Helper\Model as BackendSearchModel;
use ForkCMS\Modules\Tags\Backend\Helper\Model as BackendTagsModel;

/**
 * This is the add-action, it will display a form to create a new item
 */
class Add extends BackendBaseActionAdd
{
    public function execute(): void
    {
        parent::execute();

        $this->loadForm();
        $this->validateForm();

        $this->parse();
        $this->display();
    }

    private function loadForm(): void
    {
        // create form
        $this->form = new BackendForm('add');

        // set hidden values
        $rbtHiddenValues = [
            ['label' => BL::lbl('Hidden'), 'value' => 1],
            ['label' => BL::lbl('Published'), 'value' => 0],
        ];
        // get categories
        $categories = BackendFaqModel::getCategories();

        // create elements
        $this->form->addText('title', null, null, 'form-control title', 'form-control title is-invalid')->makeRequired();
        $this->form->addEditor('answer')->makeRequired();
        $this->form->addRadiobutton('hidden', $rbtHiddenValues, 0);
        $this->form->addDropdown('category_id', $categories);
        $this->form->addText('tags', null, null, 'form-control js-tags-input', 'form-control danger js-tags-input');

        // meta
        $this->meta = new BackendMeta($this->form, null, 'title', true);
    }

    protected function parse(): void
    {
        parent::parse();

        // get url
        $url = BackendModel::getUrlForBlock($this->url->getModule(), 'Detail');
        $url404 = BackendModel::getUrl(Page::ERROR_PAGE_ID);

        // parse additional variables
        if ($url404 != $url) {
            $this->template->assign('detailURL', SITE_URL . $url);
        }
    }

    private function validateForm(): void
    {
        if ($this->form->isSubmitted()) {
            $this->form->cleanupFields();

            // validate fields
            $this->form->getField('title')->isFilled(BL::err('QuestionIsRequired'));
            $this->form->getField('answer')->isFilled(BL::err('AnswerIsRequired'));
            $this->form->getField('category_id')->isFilled(BL::err('CategoryIsRequired'));
            $this->meta->validate();

            if ($this->form->isCorrect()) {
                // build item
                $item = [];
                $item['meta_id'] = $this->meta->save();
                $item['category_id'] = $this->form->getField('category_id')->getValue();
                $item['user_id'] = BackendAuthentication::getUser()->getUserId();
                $item['language'] = BL::getWorkingLanguage();
                $item['question'] = $this->form->getField('title')->getValue();
                $item['answer'] = $this->form->getField('answer')->getValue(true);
                $item['created_on'] = BackendModel::getUTCDate();
                $item['hidden'] = $this->form->getField('hidden')->getValue();
                $item['sequence'] = BackendFaqModel::getMaximumSequence(
                    $this->form->getField('category_id')->getValue()
                ) + 1;

                // save the data
                $item['id'] = BackendFaqModel::insert($item);
                BackendTagsModel::saveTags(
                    $item['id'],
                    $this->form->getField('tags')->getValue(),
                    $this->url->getModule()
                );

                // add search index
                BackendSearchModel::saveIndex(
                    $this->getModule(),
                    $item['id'],
                    [
                        'title' => $item['question'],
                        'text' => $item['answer'],
                    ]
                );
                $this->redirect(
                    BackendModel::createUrlForAction('Index') . '&report=added&var=' .
                    rawurlencode($item['question']) . '&highlight=' . $item['id']
                );
            }
        }
    }
}
