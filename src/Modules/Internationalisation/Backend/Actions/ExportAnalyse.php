<?php

namespace ForkCMS\Modules\Locale\Backend\Actions;

use ForkCMS\Core\Backend\Domain\Action\ActionIndex as BackendBaseActionIndex;
use ForkCMS\Modules\Locale\Backend\Domain\Translator\Language as BL;
use ForkCMS\Core\Backend\Helper\Model as BackendModel;
use ForkCMS\Modules\Locale\Domain\Translator\AnalyseModel as BackendLocaleModel;
use Symfony\Component\HttpFoundation\Response;

/**
 * This is the export-action, it will create a XML with missing locale items.
 */
class ExportAnalyse extends BackendBaseActionIndex
{
    /**
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
        $this->checkToken();
        $this->setFilter();
        $this->setItems();
    }

    /**
     * Sets the filter based on the $_GET array.
     */
    private function setFilter(): void
    {
        $this->filter['language'] = $this->getRequest()->query->has('language')
            ? $this->getRequest()->query->get('language')
            : BL::getWorkingLanguage();
    }

    /**
     * Build items array and group all items by application, module, type and name.
     */
    private function setItems(): void
    {
        $this->locale = [];

        // get items
        $frontend = BackendLocaleModel::getNonExistingFrontendLocale($this->filter['language']);

        // group by application, module, type and name
        foreach ($frontend as $item) {
            $item['value'] = null;

            $this->locale[$item['application']][$item['module']][$item['type']][$item['name']][] = $item;
        }

        // no need to keep this around
        unset($frontend);

        // get items
        $backend = BackendLocaleModel::getNonExistingBackendLocale($this->filter['language']);

        // group by application, module, type and name
        foreach ($backend as $item) {
            $item['value'] = null;

            $this->locale[$item['application']][$item['module']][$item['type']][$item['name']][] = $item;
        }

        // no need to keep this around
        unset($backend);
    }
}