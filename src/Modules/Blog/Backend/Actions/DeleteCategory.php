<?php

namespace ForkCMS\Modules\Blog\Backend\Actions;

use ForkCMS\Core\Backend\Domain\Action\ActionDelete as BackendBaseActionDelete;
use ForkCMS\Core\Backend\Helper\Model as BackendModel;
use ForkCMS\Core\Backend\Domain\Form\DeleteType;
use ForkCMS\Modules\Blog\Backend\Helper\Model as BackendBlogModel;

/**
 * This action will delete a category
 */
class DeleteCategory extends BackendBaseActionDelete
{
    public function execute(): void
    {
        $deleteForm = $this->createForm(
            DeleteType::class,
            null,
            ['module' => $this->getModule(), 'action' => 'DeleteCategory']
        );
        $deleteForm->handleRequest($this->getRequest());
        if (!$deleteForm->isSubmitted() || !$deleteForm->isValid()) {
            $this->redirect(BackendModel::createUrlForAction(
                'Categories',
                null,
                null,
                ['error' => 'something-went-wrong']
            ));

            return;
        }
        $deleteFormData = $deleteForm->getData();

        $this->id = (int) $deleteFormData['id'];

        // does the item exist
        if ($this->id === 0 || !BackendBlogModel::existsCategory($this->id)) {
            $this->redirect(BackendModel::createUrlForAction('Categories', null, null, ['error' => 'non-existing']));

            return;
        }

        $this->record = (array) BackendBlogModel::getCategory($this->id);

        // allowed to delete the category?
        if (!BackendBlogModel::deleteCategoryAllowed($this->id)) {
            $this->redirect(BackendModel::createUrlForAction(
                'Categories',
                null,
                null,
                ['error' => 'delete-category-not-allowed', 'var' => $this->record['title']]
            ));

            return;
        }

        parent::execute();

        BackendBlogModel::deleteCategory($this->id);

        $this->redirect(BackendModel::createUrlForAction(
            'Categories',
            null,
            null,
            ['report' => 'deleted-category', 'var' => $this->record['title']]
        ));
    }
}
