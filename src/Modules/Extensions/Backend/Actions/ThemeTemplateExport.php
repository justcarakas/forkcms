<?php

namespace ForkCMS\Modules\Extensions\Backend\Actions;

use DOMDocument;
use ForkCMS\Modules\Backend\Domain\Action\AbstractActionController;
use ForkCMS\Modules\Extensions\Domain\Theme\Theme;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ThemeTemplateExport extends AbstractActionController
{
    protected function execute(Request $request): void
    {
    }

    public function getResponse(Request $request): Response
    {
        $theme = $this->getEntityFromRequest($request, Theme::class);
        $xml = new DOMDocument('1.0', 'utf-8');
        $xml->formatOutput = true;
        $xml->preserveWhiteSpace = false;
        $templatesXml = $xml->createElement('templates');
        $xml->appendChild($templatesXml);
        foreach ($theme->getTemplates() as $template) {
            $templateXml = $xml->createElement('template');
            $templateXml->setAttribute('name', $template->getName());
            $templateXml->setAttribute('path', $template->getPath());
            $templatesXml->appendChild($templateXml);
            $templateXml->appendChild(
                $xml->createElement('format', $template->getSetting('format'))
            );
        }

        return new Response(
            $xml->saveXML(),
            Response::HTTP_OK,
            [
                'Content-type' => 'text/xml',
                'Content-disposition' => 'attachment; filename="templates_' . gmdate('Y-m-d', null) . '.xml"',
            ]
        );
    }
}
