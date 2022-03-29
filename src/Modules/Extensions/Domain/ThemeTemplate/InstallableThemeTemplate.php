<?php

namespace ForkCMS\Modules\Extensions\Domain\ThemeTemplate;

use SimpleXMLElement;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Serializer;

final class InstallableThemeTemplate extends ThemeTemplateDataTransferObject
{
    public static function fromXML(SimpleXMLElement|bool|null $template): ?self
    {
        if (!$template instanceof SimpleXMLElement) {
            return null;
        }
        $sanitiser = new HtmlSanitizer((new HtmlSanitizerConfig()));

        $themeTemplate = new self();
        $themeTemplate->name = (string) $template->attributes()->name;
        $themeTemplate->path = str_replace(ThemeTemplate::PATH_DIRECTORY, '', $template->attributes()->path);
        $themeTemplate->active = true;
        $layout = $sanitiser->sanitize(str_replace(' ', '', trim($template->layout)));
        $themeTemplate->settings->set('layout', $layout);
        $positions = [];
        $serialiser = new Serializer([], [new XmlEncoder()]);
        foreach ($template->positions as $xmlPositions) {
            foreach ($xmlPositions->position as $xmlPosition) {
                $blocks = [];
                foreach ($xmlPosition->block as $xmlBlock) {
                    $blocks[] = $serialiser->decode($xmlBlock->saveXML(), 'xml');
                }
                $positions[] = [
                    'name' => (string) $xmlPosition->attributes()->name,
                    'blocks' => $blocks,
                ];
            }
        }
        if (count($positions) === 0) {
            $positions = array_map(
                static fn (string $position): array => ['name' => $position],
                ThemeTemplateDataTransferObject::getPositionsFromFormat($layout)
            );
        }
        $themeTemplate->settings->set('positions', $positions);

        return $themeTemplate;
    }
}
