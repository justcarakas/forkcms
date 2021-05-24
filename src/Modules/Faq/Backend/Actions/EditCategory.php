<?php

namespace ForkCMS\Modules\Faq\Backend\Actions;

use ForkCMS\Core\Backend\Domain\Action\ActionEdit as BackendBaseActionEdit;
use ForkCMS\Modules\Authentication\Backend\Domain\Authentication\Authentication as BackendAuthentication;
use ForkCMS\Core\Backend\Domain\Form\Form as BackendForm;
use ForkCMS\Core\Backend\Domain\Meta\Meta as BackendMeta;
use ForkCMS\Modules\Locale\Backend\Domain\Translator\Language as BL;
use ForkCMS\Core\Backend\Helper\Model as BackendModel;
use ForkCMS\Core\Backend\Domain\Form\DeleteType;
use ForkCMS\Modules\Faq\Backend\Helper\Model as BackendFaqModel;
use ForkCMS\Modules\Pages\Domain\Page\Page;

/**
 * This is the edit category action, it will display a form to edit an existing category.
 */
class EditCategory extends BackendBaseActionEdit
{
    public function execute(): void
    {
        $this->id = $this->getRequest()->query->getInt('id');

        // does the item exist?
        if ($this->id !== 0 && BackendFaqModel::existsCategory($this->id)) {
            parent::execute();

            $this->getData();
            $this->loadForm();
            $this->validateForm();
            $this->loadDeleteForm();

            $this->parse();
            $this->display();
        } else {
            $this->redirect(BackendModel::createUrlForAction('Categories') . '&error=non-existing');
        }
    }

    private function getData(): void
    {
        $this->record = BackendFaqModel::getCategory($this->id);
    }

    private function loadForm(): void
    {
        // create form
        $this->form = new BackendForm('editCategory');
        $this->form->addText('title', $this->record['title'])->makeRequired();

        $this->meta = new BackendMeta($this->form, $this->record['meta_id'], 'title', true);
    }

    protected function parse(): void
    {
        parent::parse();

        // assign the data
        $this->template->assign('item', $this->record);
        $this->template->assign(
            'showFaqDeleteCategory',
            (
                BackendFaqModel::deleteCategoryAllowed($this->id) &&
                BackendAuthentication::isAllowedAction('DeleteCategory')
            )
        );

        $url = BackendModel::getUrlForBlock($this->url->getModule(), 'Category');
        $url404 = BackendModel::getUrl(Page::ERROR_PAGE_ID);
        if ($url404 != $url) {
            $this->template->assign('detailURL', SITE_URL . $url);
        }

        $this->header->appendDetailToBreadcrumbs($this->record['title']);
    }

    private function validateForm(): void
    {
        if ($this->form->isSubmitted()) {
            $this->meta->setUrlCallback(
                'Backend\Modules\Faq\Engine\Model',
                'getUrlForCategory',
                [$this->record['id']]
            );

            $this->form->cleanupFields();

            // validate fields
            $this->form->getField('title')->isFilled(BL::err('TitleIsRequired'));
            $this->meta->validate();

            if ($this->form->isCorrect()) {
                // build item
                $item = [];
                $item['id'] = $this->id;
                $item['language'] = $this->record['language'];
                $item['title'] = $this->form->getField('title')->getValue();
                $item['extra_id'] = $this->record['extra_id'];
                $item['meta_id'] = $this->meta->save(true);

                // update the item
                BackendFaqModel::updateCategory($item);

                // everything is saved, so redirect to the overview
                $this->redirect(
                    BackendModel::createUrlForAction('Categories') . '&report=edited-category&var=' .
                    rawurlencode($item['title']) . '&highlight=row-' . $item['id']
                );
            }
        }
    }

    private function loadDeleteForm(): void
    {
        $deleteForm = $this->createForm(
            DeleteType::class,
            ['id' => $this->record['id']],
            ['module' => $this->getModule(), 'action' => 'DeleteCategory']
        );
        $this->template->assign('deleteForm', $deleteForm->createView());
    }
}
