<?php

namespace ForkCMS\Modules\Pages\Backend\Actions;

use ForkCMS\Core\Backend\Domain\Action\ActionDelete as BackendBaseActionDelete;
use ForkCMS\Core\Backend\Helper\Model as BackendModel;
use ForkCMS\Modules\Internationalisation\Backend\Domain\Locale\Locale;
use ForkCMS\Core\Backend\Domain\Form\DeleteType;
use ForkCMS\Modules\Pages\Backend\Helper\Model as BackendPagesModel;
use ForkCMS\Modules\Search\Backend\Helper\Model as BackendSearchModel;

/**
 * This is the delete-action, it will delete a page
 * @TODO clean up this action
 */
class PageDelete extends BackendBaseActionDelete
{
    public function execute(): void
    {
        $deleteForm = $this->createForm(
            DeleteType::class,
            ['id' => null],
            ['module' => $this->getModule()]
        );
        $deleteForm->handleRequest($this->getRequest());
        if (!$deleteForm->isSubmitted() || !$deleteForm->isValid()) {
            $this->redirect(BackendModel::createUrlForAction('PageIndex', null, null, ['error' => 'something-went-wrong']));

            return;
        }
        $deleteFormData = $deleteForm->getData();

        $this->id = (int) $deleteFormData['id'];

        // does the item exist
        if ($this->id === 0 || !BackendPagesModel::exists($this->id)) {
            $this->redirect(BackendModel::createUrlForAction('PageIndex', null, null, ['error' => 'non-existing']));

            return;
        }

        parent::execute();

        // cannot have children
        if (BackendPagesModel::getFirstChildId($this->id) !== null) {
            $this->redirect(BackendModel::createUrlForAction('PageIndex', null, null, ['error' => 'non-existing']));

            return;
        }

        $revisionId = $this->getRequest()->query->getInt('revision_id');
        if ($revisionId === 0) {
            $revisionId = null;
        }

        $page = BackendPagesModel::get($this->id, $revisionId);

        if (empty($page)) {
            $this->redirect(BackendModel::createUrlForAction('PageIndex', null, null, ['error' => 'non-existing']));

            return;
        }

        $success = BackendPagesModel::delete($this->id, null, $revisionId);

        // delete search indexes
        BackendSearchModel::removeIndex($this->getModule(), $this->id);

        // build cache
        BackendPagesModel::buildCache(Locale::workingLocale());

        if (!$success) {
            $this->redirect(BackendModel::createUrlForAction('PageIndex', null, null, ['error' => 'non-existing']));

            return;
        }

        $this->redirect(BackendModel::createUrlForAction(
            'PageIndex',
            null,
            null,
            ['id' => $page['parent_id'], 'report' => 'deleted', 'var' => $page['title']]
        ));
    }
}
