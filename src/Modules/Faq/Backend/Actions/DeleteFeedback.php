<?php

namespace ForkCMS\Modules\Faq\Backend\Actions;

use ForkCMS\Core\Backend\Domain\Action\ActionDelete as BackendBaseActionDelete;
use ForkCMS\Core\Backend\Helper\Model as BackendModel;
use ForkCMS\Core\Backend\Domain\Form\DeleteType;
use ForkCMS\Modules\Faq\Backend\Helper\Model as BackendFaqModel;

/**
 * This is the DeleteFeedback action, it will display a form to create a new item
 */
class DeleteFeedback extends BackendBaseActionDelete
{
    public function execute(): void
    {
        $deleteForm = $this->createForm(
            DeleteType::class,
            null,
            ['module' => $this->getModule(), 'action' => 'DeleteFeedback']
        );
        $deleteForm->handleRequest($this->getRequest());
        if (!$deleteForm->isSubmitted() || !$deleteForm->isValid()) {
            $this->redirect(BackendModel::createUrlForAction('Index', null, null, ['error' => 'something-went-wrong']));

            return;
        }
        $deleteFormData = $deleteForm->getData();

        $feedbackId = (int) $deleteFormData['id'];
        $feedback = BackendFaqModel::getFeedback($feedbackId);

        // there is no feedback data, so redirect
        if (empty($feedback)) {
            $this->redirect(BackendModel::createUrlForAction('Index', null, null, ['error' => 'non-existing']));
        }

        BackendFaqModel::deleteFeedback($feedbackId);
        $this->redirect(BackendModel::createUrlForAction(
            'Edit',
            null,
            null,
            ['id' => $feedback['question_id'], 'report' => 'deleted']
        ) . '#tabFeedback');
    }
}
