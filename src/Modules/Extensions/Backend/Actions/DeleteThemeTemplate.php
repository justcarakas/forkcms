<?php

namespace ForkCMS\Modules\Extensions\Backend\Actions;

use ForkCMS\Core\Backend\Domain\Action\ActionDelete as BackendBaseActionDelete;
use ForkCMS\Core\Backend\Helper\Model as BackendModel;
use ForkCMS\Core\Backend\Domain\Form\DeleteType;
use ForkCMS\Modules\Extensions\Backend\Helper\Model as BackendExtensionsModel;

/**
 * This is the delete-action, it will delete a template
 */
class DeleteThemeTemplate extends BackendBaseActionDelete
{
    public function execute(): void
    {
        $deleteForm = $this->createForm(
            DeleteType::class,
            null,
            ['module' => $this->getModule(), 'action' => 'DeleteThemeTemplate']
        );
        $deleteForm->handleRequest($this->getRequest());
        if (!$deleteForm->isSubmitted() || !$deleteForm->isValid()) {
            $this->redirect(BackendModel::createUrlForAction(
                'ThemeTemplates',
                null,
                null,
                ['error' => 'something-went-wrong']
            ));

            return;
        }
        $deleteFormData = $deleteForm->getData();

        $this->id = (int) $deleteFormData['id'];

        // does the item exist
        if ($this->id === 0 || !BackendExtensionsModel::existsTemplate($this->id)) {
            $this->redirect(BackendModel::createUrlForAction(
                'ThemeTemplates',
                null,
                null,
                ['error' => 'non-existing']
            ));

            return;
        }

        parent::execute();

        $success = false;
        $item = BackendExtensionsModel::getTemplate($this->id);
        if (!empty($item)) {
            $imagePath = FRONTEND_FILES_PATH . '/Templates/images';
            BackendModel::deleteThumbnails($imagePath, $item['default_image']);

            $success = BackendExtensionsModel::deleteTemplate($this->id);
        }

        if (!$success) {
            $this->redirect(BackendModel::createUrlForAction(
                'ThemeTemplates',
                null,
                null,
                ['error' => 'non-existing']
            ));

            return;
        }

        $this->redirect(BackendModel::createUrlForAction(
            'ThemeTemplates',
            null,
            null,
            ['theme' => $item['theme'], 'report' => 'deleted-template', 'var' => $item['label']]
        ));
    }
}
