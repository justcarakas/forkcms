<?php

namespace ForkCMS\Modules\Extensions\Domain\ThemeTemplate;

use SimpleXMLElement;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

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
        $themeTemplate->path = (string) $template->attributes()->path;
        $themeTemplate->active = true;
        $format = $sanitiser->sanitize(str_replace(' ', '', trim($template->format)));
        $themeTemplate->settings->set('format', $format);
        $themeTemplate->settings->set('positions', array_values(self::getPositionsFromFormat($format)));

        return $themeTemplate;
    }
}
