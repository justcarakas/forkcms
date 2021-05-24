<?php

namespace ForkCMS\Modules\Faq\Backend\Ajax;

use ForkCMS\Core\Backend\Domain\Ajax\AjaxAction as BackendBaseAJAXAction;
use ForkCMS\Modules\Locale\Backend\Domain\Translator\Language;
use ForkCMS\Modules\Faq\Backend\Helper\Model as BackendFaqModel;
use Symfony\Component\HttpFoundation\Response;

/**
 * Reorder questions
 */
class SequenceQuestions extends BackendBaseAJAXAction
{
    public function execute(): void
    {
        parent::execute();

        $questionId = $this->getRequest()->request->getInt('questionId');
        $fromCategoryId = $this->getRequest()->request->getInt('fromCategoryId');
        $toCategoryId = $this->getRequest()->request->getInt('toCategoryId');
        $fromCategorySequence = $this->getRequest()->request->get('fromCategorySequence', '');
        $toCategorySequence = $this->getRequest()->request->get('toCategorySequence', '');

        // invalid question id
        if (!BackendFaqModel::exists($questionId)) {
            $this->output(Response::HTTP_BAD_REQUEST, null, 'question does not exist');

            return;
        }

        // list ids
        $fromCategorySequence = (array) explode(',', ltrim($fromCategorySequence, ','));
        $toCategorySequence = (array) explode(',', ltrim($toCategorySequence, ','));

        // is the question moved to a new category?
        if ($fromCategoryId != $toCategoryId) {
            $item = [];
            $item['id'] = $questionId;
            $item['category_id'] = $toCategoryId;

            BackendFaqModel::update($item);

            // loop id's and set new sequence
            foreach ($toCategorySequence as $i => $id) {
                $item = [];
                $item['id'] = (int) $id;
                $item['sequence'] = $i + 1;

                // update sequence if the item exists
                if (BackendFaqModel::exists($item['id'])) {
                    BackendFaqModel::update($item);
                }
            }
        }

        // loop id's and set new sequence
        foreach ($fromCategorySequence as $i => $id) {
            $item['id'] = (int) $id;
            $item['sequence'] = $i + 1;

            // update sequence if the item exists
            if (BackendFaqModel::exists($item['id'])) {
                BackendFaqModel::update($item);
            }
        }

        // success output
        $this->output(Response::HTTP_OK, null, Language::msg('SequenceSaved'));
    }
}
