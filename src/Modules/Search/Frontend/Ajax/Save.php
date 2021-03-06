<?php

namespace ForkCMS\Modules\Search\Frontend\Ajax;

use ForkCMS\Core\Frontend\Helper\Base\AjaxAction as FrontendBaseAJAXAction;
use ForkCMS\Core\Frontend\Helper\Model as FrontendModel;
use ForkCMS\Modules\Search\Frontend\Helper\Model as FrontendSearchModel;
use Symfony\Component\HttpFoundation\Response;

/**
 * This is the save-action, it will save the searched term in the statistics
 */
class Save extends FrontendBaseAJAXAction
{
    public function execute(): void
    {
        parent::execute();

        $charset = $this->getContainer()->getParameter('kernel.charset');
        $searchTerm = $this->getRequest()->request->get('term', '');
        $term = ($charset === 'utf-8')
            ? \SpoonFilter::htmlspecialchars($searchTerm) : \SpoonFilter::htmlentities($searchTerm);

        if ($term === '') {
            $this->output(Response::HTTP_BAD_REQUEST, null, 'term-parameter is missing.');

            return;
        }

        if ($this->isNewSearchTerm($term)) {
            FrontendSearchModel::save(
                [
                    'term' => $term,
                    'language' => LANGUAGE,
                    'time' => FrontendModel::getUTCDate(),
                    'data' => serialize(['server' => $_SERVER]),
                    'num_results' => FrontendSearchModel::getTotal($term),
                ]
            );
        }

        FrontendModel::getSession()->set('searchTerm', $term);

        $this->output(Response::HTTP_OK);
    }

    private function isNewSearchTerm(string $term): bool
    {
        $previousTerm = FrontendModel::getSession()->get('searchTerm', '');
        FrontendModel::getSession()->set('searchTerm', '');

        return $term !== $previousTerm;
    }
}
