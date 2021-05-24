<?php

namespace ForkCMS\Modules\Blog\Backend\Actions;

use ForkCMS\Core\Backend\Domain\Action\ActionEdit as BackendBaseActionEdit;
use ForkCMS\Core\Backend\Helper\Model as BackendModel;
use ForkCMS\Core\Backend\Domain\Form\Form as BackendForm;
use ForkCMS\Core\Backend\Domain\Meta\Meta as BackendMeta;
use ForkCMS\Modules\Locale\Backend\Domain\Translator\Language as BL;
use ForkCMS\Core\Backend\Domain\Form\DeleteType;
use ForkCMS\Modules\Blog\Backend\Helper\Model as BackendBlogModel;

/**
 * This is the edit category action, it will display a form to edit an existing category.
 */
class EditCategory extends BackendBaseActionEdit
{
    public function execute(): void
    {
        // get parameters
        $this->id = $this->getRequest()->query->getInt('id');

        // does the item exists
        if ($this->id !== 0 && BackendBlogModel::existsCategory($this->id)) {
            parent::execute();
            $this->getData();
            $this->loadForm();
            $this->validateForm();
            $this->loadDeleteForm();
            $this->parse();
            $this->display();
        } else {
            // no item found, throw an exception, because somebody is fucking with our URL
            $this->redirect(BackendModel::createUrlForAction('Index') . '&error=non-existing');
        }
    }

    private function getData(): void
    {
        $this->record = BackendBlogModel::getCategory($this->id);
    }

    private function loadForm(): void
    {
        // create form
        $this->form = new BackendForm('editCategory');

        // create elements
        $this->form->addText('title', $this->record['title'], null, 'form-control title', 'form-control danger title')->makeRequired();

        // meta object
        $this->meta = new BackendMeta($this->form, $this->record['meta_id'], 'title', true);

        // set callback for generating a unique URL
        $this->meta->setUrlCallback('Backend\Modules\Blog\Engine\Model', 'getUrlForCategory', [$this->record['id']]);
    }

    protected function parse(): void
    {
        parent::parse();

        $this->template->assign('item', $this->record);

        // delete allowed?
        $this->template->assign(
            'allowBlogDeleteCategory',
            BackendBlogModel::deleteCategoryAllowed($this->id)
        );

        // parse base url for preview
        $url = BackendModel::getUrlForBlock($this->url->getModule(), 'Category');
        $url404 = BackendModel::getUrl(404);
        if ($url404 !== $url) {
            $this->template->assign('detailURL', SITE_URL . $url);
            $this->template->assign('categorySlug', $this->meta->getUrl());
        }

        $this->header->appendDetailToBreadcrumbs($this->record['title']);
    }

    private function validateForm(): void
    {
        if ($this->form->isSubmitted()) {
            // cleanup the submitted fields, ignore fields that were added by hackers
            $this->form->cleanupFields();

            // validate fields
            $this->form->getField('title')->isFilled(BL::err('TitleIsRequired'));

            // validate meta
            $this->meta->validate();

            if ($this->form->isCorrect()) {
                // build item
                $item = [
                    'id' => $this->id,
                    'title' => $this->form->getField('title')->getValue(),
                    'meta_id' => $this->meta->save(true),
                ];

                // update the item
                BackendBlogModel::updateCategory($item);

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
