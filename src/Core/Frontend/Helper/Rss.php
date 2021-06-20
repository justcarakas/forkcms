<?php

namespace ForkCMS\Core\Frontend\Helper;

use ForkCMS\Modules\Extensions\Domain\ModuleSetting\ModuleSettingRepository;
use ForkCMS\Core\Common\Uri as CommonUri;

/**
 * Frontend RSS class.
 */
class Rss extends \SpoonFeedRSS
{
    public function __construct(string $title, string $link, string $description, array $items = [])
    {
        // decode
        $title = \SpoonFilter::htmlspecialcharsDecode($title);
        $description = \SpoonFilter::htmlspecialcharsDecode($description);

        // call the parent
        parent::__construct(
            $title,
            str_replace(
                '&',
                '&amp;',
                Model::addUrlParameters(
                    $link,
                    ['utm_source' => 'feed', 'utm_medium' => 'rss', 'utm_campaign' => CommonUri::getUrl($title)],
                    '&amp;'
                )
            ),
            $description,
            $items
        );

        $siteTitle = \SpoonFilter::htmlspecialcharsDecode(
            Model::get(ModuleSettingRepository::class)->get('Core', 'site_title_' . LANGUAGE)
        );

        // set feed properties
        $this->setLanguage(LANGUAGE);
        $this->setCopyright(\SpoonDate::getDate('Y') . ' ' . $siteTitle);
        $this->setGenerator($siteTitle);
        $this->setImage(SITE_URL . FRONTEND_CORE_URL . '/Layout/images/rss_image.png', $title, $link);

        // theme was set
        if (Model::get(ModuleSettingRepository::class)->get('Core', 'theme', null) === null) {
            return;
        }

        // theme name
        $theme = Model::get(ModuleSettingRepository::class)->get('Core', 'theme', 'Fork');

        // theme rss image exists
        if (is_file(PATH_WWW . '/src/Frontend/Themes/' . $theme . '/Core/Images/rss_image.png')) {
            // set rss image
            $this->setImage(
                SITE_URL . '/src/Frontend/Themes/' . $theme . '/Core/Images/rss_image.png',
                $title,
                $link
            );
        }
    }

    public function setImage($url, $title, $link, $width = null, $height = null, $description = null): void
    {
        // add UTM-parameters
        $link = Model::addUrlParameters(
            $link,
            [
                'utm_source' => 'feed',
                'utm_medium' => 'rss',
                'utm_campaign' => CommonUri::getUrl($this->getTitle()),
            ],
            '&amp;'
        );

        // call the parent
        parent::setImage($url, $title, $link, $width, $height, $description);
    }
}
