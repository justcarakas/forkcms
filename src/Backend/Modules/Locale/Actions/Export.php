<?php

namespace Backend\Modules\Locale\Actions;

use Backend\Core\Engine\Base\ActionIndex as BackendBaseActionIndex;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Modules\Locale\Engine\Model as BackendLocaleModel;
use Backend\Modules\Locale\Engine\Model;
use Symfony\Component\HttpFoundation\Response;

/**
 * This is the export-action, it will create a XML with locale items.
 */
class Export extends BackendBaseActionIndex
{
    /**
     * Filter variables.
     *
     * @var array
     */
    private $filter;

    /**
     * Locale items.
     *
     * @var array
     */
    private $locale;

    /**
     * Create the XML based on the locale items.
     *
     * @return Response
     */
    public function getContent(): Response
    {
        $charset = BackendModel::getContainer()->getParameter('kernel.charset');

        // create XML
        $xmlOutput = BackendLocaleModel::createXMLForExport($this->locale);

        return new Response(
            $xmlOutput,
            Response::HTTP_OK,
            [
                'Content-Disposition' => 'attachment; filename="locale_' . BackendModel::getUTCDate('d-m-Y') . '.xml"',
                'Content-Type' => 'application/octet-stream;charset=' . $charset,
                'Content-Length' => '' . mb_strlen($xmlOutput),
            ]
        );
    }

    public function execute(): void
    {
        $this->setFilter();
        $this->setItems();
    }

    /**
     * Sets the filter based on the $_GET array.
     */
    private function setFilter(): void
    {
        $this->filter['language'] = $this->getRequest()->query->get('language', []);
        if (empty($this->filter['language'])) {
            $this->filter['language'] = BL::getWorkingLanguage();
        }
        $this->filter['application'] = $this->getRequest()->query->get('application');
        $this->filter['module'] = $this->getRequest()->query->get('module');
        $this->filter['type'] = $this->getRequest()->query->get('type', '');
        if ($this->filter['type'] === '') {
            $this->filter['type'] = null;
        }
        $this->filter['name'] = $this->getRequest()->query->get('name');
        $this->filter['value'] = $this->getRequest()->query->get('value');

        $ids = $this->getRequest()->query->get('ids', '');
        if ($ids === '') {
            $ids = [];
        } else {
            $ids = explode('|', $ids);
        }
        $this->filter['ids'] = $ids;

        foreach ($this->filter['ids'] as $id) {
            // someone is messing with the url, clear ids
            if (!is_numeric($id)) {
                $this->filter['ids'] = [];
                break;
            }
        }
    }

    /**
     * Build items array and group all items by application, module, type and name.
     */
    private function setItems(): void
    {
        // get locale from the database
        $items = Model::getLocaleForExport($this->filter);

        // init
        $this->locale = [];

        // group by application, module, type and name
        foreach ($items as $item) {
            $this->locale[$item['application']][$item['module']][$item['type']][$item['name']][] = $item;
        }

        // no need to keep this around
        unset($items);
    }
}
