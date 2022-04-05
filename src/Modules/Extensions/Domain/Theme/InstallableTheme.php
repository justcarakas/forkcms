<?php

namespace ForkCMS\Modules\Extensions\Domain\Theme;

use ForkCMS\Modules\Extensions\Domain\InformationFile\Author;
use ForkCMS\Modules\Extensions\Domain\InformationFile\Messages;
use ForkCMS\Modules\Extensions\Domain\InformationFile\Requirements;
use ForkCMS\Modules\Extensions\Domain\InformationFile\SafeHtml;
use ForkCMS\Modules\Extensions\Domain\InformationFile\SafeString;
use ForkCMS\Modules\Extensions\Domain\ThemeTemplate\InstallableThemeTemplate;
use ForkCMS\Modules\Internationalisation\Domain\Translation\TranslationKey;

final class InstallableTheme extends ThemeDataTransferObject
{
    private readonly Messages $messages;

    protected function __construct(?Theme $themeEntity = null)
    {
        parent::__construct($themeEntity);

        $this->messages = new Messages();
    }

    public static function fromTheme(Theme $theme): self
    {
        return new self($theme);
    }

    public static function fromXML(string $xmlFilePath): self
    {
        $themeConfig = simplexml_load_string(file_get_contents($xmlFilePath), 'SimpleXMLElement', LIBXML_NOCDATA);
        $theme = new self();
        Requirements::fromXML($themeConfig->requirements, $theme->messages);
        $theme->name = SafeString::fromXML($themeConfig->name)->string;
        $directoryName = basename(dirname($xmlFilePath));
        if ($theme->name !== $directoryName) {
            $theme->addMessage(TranslationKey::error('ThemeNameDoesntMatch'));
        }
        $thumbnail = realpath(
            dirname($xmlFilePath) . '/assets/public/' . SafeString::fromXML($themeConfig->thumbnail)->string
        );
        if (
            !is_dir($thumbnail)
            && getimagesize($thumbnail) !== false
            && str_starts_with($thumbnail, dirname($xmlFilePath))
        ) {
            $theme->settings->set(
                'thumbnail',
                sprintf(
                    '@%1$sTheme:%2$s',
                    $theme->name,
                    substr($thumbnail, strpos($thumbnail, '/assets/public/') + strlen('/assets/public/'))
                )
            );
        }
        $themeVersion = SafeString::fromXML($themeConfig->version)->string;
        if ($themeVersion !== '') {
            $theme->settings->set('themeVersion', $themeVersion);
        }
        $theme->settings->set(
            'metaNavigation',
            ((string) $themeConfig->meta_navigation->attributes()->enabled) === 'true'
        );
        $authors = [];
        foreach ($themeConfig->authors->author as $authorConfig) {
            $authors[] = Author::fromXML($authorConfig);
        }
        if (count($authors) > 0) {
            $theme->settings->set('authors', $authors);
        }
        $theme->description = SafeHtml::fromXML($themeConfig->description);
        $theme->active = false;
        $templates = ['default' => [], 'other' => []];
        foreach ($themeConfig->templates[0] as $template) {
            $themeTemplate = InstallableThemeTemplate::fromXML($template);
            if ($themeTemplate !== null) {
                $templates[$themeTemplate->isDefault ? 'default' : 'other'][$themeTemplate->name] = $themeTemplate;
            }
        }
        // we need the default template to be first so it will become the default one
        foreach ($templates as $templateList) {
            foreach ($templateList as $template) {
                $theme->templates[$template->name] = $template;
            }
        }

        return $theme;
    }

    public static function fromMessage(TranslationKey $message): self
    {
        $theme = new self();
        $theme->addMessage($message);

        return $theme;
    }

    public function setTheme(Theme $theme): void
    {
        $this->themeEntity = $theme;
    }

    public function addMessage(TranslationKey $message): void
    {
        $this->messages->addMessage($message);
    }

    /** @return array<string, TranslationKey> */
    public function getMessages(): array
    {
        return $this->messages->getMessages();
    }

    public function isInstallable(): bool
    {
        return $this->themeEntity === null && !$this->messages->hasErrors();
    }
}
