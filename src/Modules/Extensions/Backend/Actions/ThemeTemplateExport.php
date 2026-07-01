<?php

declare(strict_types=1);

namespace ForkCMS\Modules\Extensions\Backend\Actions;

use DOMDocument;
use DOMElement;
use ForkCMS\Modules\Backend\Domain\Action\AbstractActionController;
use ForkCMS\Modules\Backend\Domain\Action\ActionServices;
use ForkCMS\Modules\Extensions\Domain\Theme\Theme;
use ForkCMS\Modules\Frontend\Domain\Block\Block;
use ForkCMS\Modules\Frontend\Domain\Block\BlockRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Export the template of a theme with their positions and the default blocks.
 */
final class ThemeTemplateExport extends AbstractActionController
{
    public function __construct(
        ActionServices $services,
        private readonly SerializerInterface $serializer,
        private readonly BlockRepository $blockRepository
    ) {
        parent::__construct($services);
    }

    #[\Override]
    protected function execute(Request $request): void
    {
    }

    #[\Override]
    public function getResponse(Request $request): Response
    {
        $theme = $this->getEntityFromRequest($request, Theme::class);
        $xml = new DOMDocument('1.0', 'utf-8');
        $xml->formatOutput = true;
        $xml->preserveWhiteSpace = false;
        $templatesXml = $xml->createElement('templates');
        $xml->appendChild($templatesXml);
        foreach ($theme->templates as $template) {
            $templateXml = $xml->createElement('template');
            $templateXml->setAttribute('name', $template->name);
            $templateXml->setAttribute('path', $template->path);
            if ($template->isDefault()) {
                $templateXml->setAttribute('default', 'true');
            }
            $templatesXml->appendChild($templateXml);
            $templateXml->appendChild(
                $xml->createElement(
                    'layout',
                    "\n      " . str_replace("\n", "\n      ", $template->getSetting('layout')) . "\n    "
                )
            );
            $positions = $template->getSetting('positions', []);
            $positionsXml = $xml->createElement('positions');
            $templateXml->appendChild($positionsXml);
            foreach ($positions as $position) {
                $positionXml = $xml->createElement('position');
                $positionXml->setAttribute('name', $position['name']);
                foreach ($position['blocks'] ?? [] as $blockId) {
                    $block = $this->blockRepository->find($blockId);
                    if ($block === null) {
                        continue;
                    }
                    $blockDOMDocument = new DOMDocument('1.0', 'utf-8');
                    $blockDOMDocument->loadXML(
                        $this->serializer->serialize(
                            $block->getSettings()->all(),
                            'xml',
                            [
                                'xml_root_node_name' => 'block'
                            ]
                        )
                    );
                    /** @var DOMElement $blockXml */
                    $blockXml = $xml->importNode($blockDOMDocument->documentElement, true);
                    $blockXml->setAttribute('module', $block->block->module->name);
                    $blockXml->setAttribute('type', $block->type->value);
                    $blockXml->setAttribute('name', (string) $block->block->name);
                    $blockXml->setAttribute('label', $this->translator->trans($block));
                    $positionXml->append($blockXml);
                }
                $positionsXml->appendChild($positionXml);
            }
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
