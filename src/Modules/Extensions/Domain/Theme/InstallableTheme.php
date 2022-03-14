<?php

namespace ForkCMS\Modules\Extensions\Domain\Theme;

use Composer\Semver\Comparator;
use ForkCMS\Modules\Extensions\Domain\ThemeTemplate\InstallableThemeTemplate;
use ForkCMS\Modules\Internationalisation\Domain\Translation\TranslationKey;
use ForkCMS\Modules\Internationalisation\Domain\Translation\Type;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

final class InstallableTheme extends ThemeDataTransferObject
{
    /** @var TranslationKey[] */
    private array $messages = [];

    public static function fromTheme(Theme $theme): self
    {
        return new self($theme);
    }

    public static function fromXML(string $xmlFilePath): self
    {
        $sanitiserSafeHtml = new HtmlSanitizer((new HtmlSanitizerConfig())->allowSafeElements());
        $sanitiserNoHtml = new HtmlSanitizer((new HtmlSanitizerConfig()));
        $themeConfig = simplexml_load_string(file_get_contents($xmlFilePath), 'SimpleXMLElement', LIBXML_NOCDATA);
        $minimumVersion = $sanitiserNoHtml->sanitize($themeConfig->requirements->minimum_version ?? '');
        $theme = new self();
        if ($minimumVersion !== '' && Comparator::lessThan($_ENV['FORK_VERSION'], $minimumVersion)) {
            $theme->addMessage(
                TranslationKey::error('InformationVersionTooLow')->withParameters(
                    ['%minimumVersion%' => $minimumVersion, '%currentVersion%' => $_ENV['FORK_VERSION']]
                )
            );
        }
        $maximumVersion = $sanitiserNoHtml->sanitize($themeConfig->requirements->maximum_version ?? '');
        if ($maximumVersion !== '' && Comparator::greaterThanOrEqualTo($_ENV['FORK_VERSION'], $maximumVersion)) {
            $theme->addMessage(
                TranslationKey::error('InformationVersionTooHigh')->withParameters(
                    ['%maximumVersion%' => $maximumVersion, '%currentVersion%' => $_ENV['FORK_VERSION']]
                )
            );
        }
        $theme->name = $sanitiserNoHtml->sanitize($themeConfig->name);
        if ($theme->name === '') {
            $theme->name = basename(dirname($xmlFilePath)); // fallback to the directory name
            $theme->addMessage(TranslationKey::error('InformationFileIsEmpty'));
        }
        $thumbnail = realpath(
            dirname($xmlFilePath) . '/assets/public/' . $sanitiserNoHtml->sanitize($themeConfig->thumbnail)
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
        $themeVersion = $sanitiserNoHtml->sanitize($themeConfig->version);
        if ($themeVersion !== '') {
            $theme->settings->set('themeVersion', $themeVersion);
        }
        $theme->settings->set(
            'metaNavigation',
            ($themeConfig->meta_navigation->attributes()->enabled ?? 'false') === 'true'
        );
        if ($minimumVersion !== '') {
            $theme->settings->set('minimumForkVersion', $minimumVersion);
        }
        if ($maximumVersion !== '') {
            $theme->settings->set('maximumForkVersion', $maximumVersion);
        }
        $authors = [];
        foreach ($themeConfig->authors as $authorConfig) {
            $authors[] = [
                'name' => $sanitiserNoHtml->sanitize($authorConfig->author->name),
                'url' => $sanitiserNoHtml->sanitize($authorConfig->author->url),
            ];
        }
        if (count($authors) > 0) {
            $theme->settings->set('authors', $authors);
        }
        $theme->description = $sanitiserSafeHtml->sanitize(nl2br(trim($themeConfig->description)));
        if ($theme->description === '') {
            $theme->description = null;
        }
        $theme->active = true;
        foreach ($themeConfig->templates[0] as $template) {
            $themeTemplate = InstallableThemeTemplate::fromXML($template);
            if ($themeTemplate !== null) {
                $theme->templates[$themeTemplate->name] = $themeTemplate;
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
        $this->messages[(string) $message->getName()] = $message;
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    public function isInstallable(): bool
    {
        return $this->themeEntity === null
            && count(
                array_filter(
                    $this->messages,
                    static fn (TranslationKey $translationKey): bool => $translationKey->getType() === Type::err
                )
            ) === 0;
    }
}
