<?php

namespace ForkCMS\Modules\Location\Frontend\Actions;

use ForkCMS\Modules\Extensions\Domain\ModuleSetting\ModuleSettingRepository;
use ForkCMS\Core\Frontend\Helper\Base\Block as FrontendBaseBlock;
use ForkCMS\Modules\Internationalisation\Frontend\Domain\Translator\Language;
use ForkCMS\Modules\Internationalisation\Frontend\Domain\Locale\Locale;
use ForkCMS\Modules\Location\Frontend\Helper\Model as FrontendLocationModel;

/**
 * This is the index-action, it has an overview of locations.
 */
class Index extends FrontendBaseBlock
{
    /**
     * @var array
     */
    protected $items = [];

    /**
     * @var array
     */
    protected $settings = [];

    public function execute(): void
    {
        // define Google Maps API key
        $apikey = $this->get(ModuleSettingRepository::class)->get('Core', 'google_maps_key');

        // check Google Maps API key, otherwise show error
        if ($apikey == null) {
            trigger_error('Please provide a Google Maps API key.');
        }

        $this->addJS(
            'https://maps.googleapis.com/maps/api/js?key=' . $apikey . '&language=' . Locale::frontendLanguage()
        );
        $this->addJS(FrontendLocationModel::getPathToMapStyles(false), true);

        parent::execute();

        $this->loadTemplate();
        $this->loadData();

        $this->parse();
    }

    protected function loadData(): void
    {
        $this->items = FrontendLocationModel::getAll();
        $this->settings = FrontendLocationModel::getMapSettings(0);
        $firstMarker = current($this->items);
        if (empty($this->settings)) {
            $this->settings = $this->get(ModuleSettingRepository::class)->getForModule('Location');
            $this->settings['center']['lat'] = $firstMarker['lat'];
            $this->settings['center']['lng'] = $firstMarker['lng'];
        }

        // no center point given yet, use the first occurrence
        if (!isset($this->settings['center'])) {
            $this->settings['center']['lat'] = $firstMarker['lat'];
            $this->settings['center']['lng'] = $firstMarker['lng'];
        }
    }

    private function parse(): void
    {
        $this->addJSData('settings', $this->settings);
        $this->addJSData('items', $this->items);

        $this->template->assign('locationItems', $this->items);
        $this->template->assign('locationSettings', $this->settings);
    }
}
