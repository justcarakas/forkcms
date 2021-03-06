<?php

namespace ForkCMS\Modules\Location\Backend\Actions;

use ForkCMS\Modules\Authentication\Backend\Domain\Authentication\Authentication as BackendAuthentication;
use ForkCMS\Core\Backend\Domain\Action\ActionEdit as BackendBaseActionEdit;
use ForkCMS\Core\Backend\Domain\Form\Form as BackendForm;
use ForkCMS\Modules\Internationalisation\Backend\Domain\Translator\Language as BL;
use ForkCMS\Core\Backend\Helper\Model as BackendModel;
use ForkCMS\Core\Backend\Domain\Form\DeleteType;
use ForkCMS\Modules\Location\Backend\Helper\Model as BackendLocationModel;
use ForkCMS\Modules\Extensions\Domain\ModuleSetting\ModuleSettingRepository;
use ForkCMS\Core\Common\ForkCMS\Utility\Geolocation;
use Symfony\Component\Intl\Intl;
use ForkCMS\Modules\Location\Frontend\Helper\Model as FrontendLocationModel;

/**
 * This is the edit-action, it will display a form to create a new item
 */
class Edit extends BackendBaseActionEdit
{
    /**
     * @var array
     */
    protected $settings = [];

    /**
     * The settings form
     *
     * @var BackendForm
     */
    protected $settingsForm;

    public function execute(): void
    {
        $this->id = $this->getRequest()->query->getInt('id');

        // does the item exists
        if ($this->id !== 0 && BackendLocationModel::exists($this->id)) {
            $this->header->addJS(FrontendLocationModel::getPathToMapStyles());
            parent::execute();

            // define Google Maps API key
            $apikey = $this->get(ModuleSettingRepository::class)->get('Core', 'google_maps_key');

            // check Google Maps API key, otherwise redirect to settings
            if ($apikey === null) {
                $this->redirect(BackendModel::createUrlForAction('Index', 'Settings'));
            }

            $this->header->addJS(
                'https://maps.googleapis.com/maps/api/js?key=' . $apikey . '&language=' . BL::getInterfaceLanguage()
            );

            $this->loadData();

            $this->loadForm();
            $this->validateForm();
            $this->loadDeleteForm();

            $this->parse();
            $this->display();
        } else {
            $this->redirect(BackendModel::createUrlForAction('Index') . '&error=non-existing');
        }
    }

    private function loadData(): void
    {
        $this->record = (array) BackendLocationModel::get($this->id);

        // no item found, throw an exceptions, because somebody is fucking with our URL
        if (empty($this->record)) {
            $this->redirect(BackendModel::createUrlForAction('Index') . '&error=non-existing');
        }

        $this->settings = BackendLocationModel::getMapSettings($this->id);

        // load the settings from the general settings
        if (empty($this->settings)) {
            $settings = $this->get(ModuleSettingRepository::class)->getForModule('Location');

            $this->settings['width'] = $settings['width_widget'];
            $this->settings['height'] = $settings['height_widget'];
            $this->settings['map_type'] = $settings['map_type_widget'];
            $this->settings['map_style'] = $settings['map_style_widget'] ?? 'standard';
            $this->settings['zoom_level'] = $settings['zoom_level_widget'];
            $this->settings['center']['lat'] = $this->record['lat'];
            $this->settings['center']['lng'] = $this->record['lng'];
        }

        // no center point given yet, use the first occurrence
        if (!isset($this->settings['center'])) {
            $this->settings['center']['lat'] = $this->record['lat'];
            $this->settings['center']['lng'] = $this->record['lng'];
        }

        $this->settings['full_url'] = $this->settings['full_url'] ?? false;
        $this->settings['directions'] = $this->settings['directions'] ?? false;
    }

    private function loadForm(): void
    {
        $this->form = new BackendForm('edit');
        $this->form->addText('title', $this->record['title'], null, 'form-control title', 'form-control danger title')->makeRequired();
        $this->form->addText('street', $this->record['street'])->makeRequired();
        $this->form->addText('number', $this->record['number'])->makeRequired();
        $this->form->addText('zip', $this->record['zip'])->makeRequired();
        $this->form->addText('city', $this->record['city'])->makeRequired();
        $this->form->addDropdown('country', Intl::getRegionBundle()->getCountryNames(BL::getInterfaceLanguage()), $this->record['country'])->makeRequired();
        $this->form->addHidden('redirect', 'overview');

        $mapTypes = [
            'ROADMAP' => BL::lbl('Roadmap', $this->getModule()),
            'SATELLITE' => BL::lbl('Satellite', $this->getModule()),
            'HYBRID' => BL::lbl('Hybrid', $this->getModule()),
            'TERRAIN' => BL::lbl('Terrain', $this->getModule()),
            'STREET_VIEW' => BL::lbl('StreetView', $this->getModule()),
        ];
        $mapStyles = [
            'standard' => BL::lbl('Default', $this->getModule()),
            'custom' => BL::lbl('Custom', $this->getModule()),
            'gray' => BL::lbl('Gray', $this->getModule()),
            'blue' => BL::lbl('Blue', $this->getModule()),
        ];

        $zoomLevels = array_combine(
            array_merge(['auto'], range(1, 18)),
            array_merge([BL::lbl('Auto', $this->getModule())], range(1, 18))
        );

        $this->form->addCheckbox('override_map_settings', $this->record['override_map_settings'])
            ->setAttributes([
                'data-target' => 'settings',
                'data-role' => 'toggle-settings',
            ]);
        // add map info (overview map)
        $this->form->addHidden('map_id', $this->id);
        $this->form->addDropdown('zoom_level', $zoomLevels, $this->settings['zoom_level']);
        $this->form->addText('width', $this->settings['width']);
        $this->form->addText('height', $this->settings['height']);
        $this->form->addHidden('centerLat', $this->settings['center']['lat'])
            ->setAttribute('data-role', 'center-lat');
        $this->form->addHidden('centerLng', $this->settings['center']['lng'])
            ->setAttribute('data-role', 'center-lng');
        $this->form->addHidden('lat', $this->record['lat']);
        $this->form->addHidden('lng', $this->record['lng']);
        $this->form->addDropdown('map_type', $mapTypes, $this->settings['map_type']);
        $this->form->addDropdown(
            'map_style',
            $mapStyles,
            $this->settings['map_style'] ?? null
        );
        $this->form->addCheckbox('full_url', $this->settings['full_url']);
        $this->form->addCheckbox('directions', $this->settings['directions']);
        $this->form->addCheckbox('marker_overview', $this->record['show_overview']);
    }

    protected function parse(): void
    {
        parent::parse();

        // assign to template
        $this->template->assign('item', $this->record);
        $this->template->assign('settings', $this->settings);
        $this->template->assign('godUser', BackendAuthentication::getUser()->isGod());

        // assign message if address was not be geocoded
        if ($this->record['lat'] == null || $this->record['lng'] == null) {
            $this->template->assign('errorMessage', BL::err('AddressCouldNotBeGeocoded'));
        }

        $this->header->appendDetailToBreadcrumbs($this->record['title']);
    }

    private function validateForm(): void
    {
        if ($this->form->isSubmitted()) {
            $this->form->cleanupFields();

            // validate fields
            $this->form->getField('title')->isFilled(BL::err('TitleIsRequired'));
            $this->form->getField('street')->isFilled(BL::err('FieldIsRequired'));
            $this->form->getField('number')->isFilled(BL::err('FieldIsRequired'));
            $this->form->getField('zip')->isFilled(BL::err('FieldIsRequired'));
            $this->form->getField('city')->isFilled(BL::err('FieldIsRequired'));

            if ($this->form->getField('override_map_settings')->isChecked()) {
                $this->form->getField('zoom_level')->isFilled(BL::err('TitleIsRequired'));
                $this->form->getField('width')->isFilled(BL::err('TitleIsRequired'));
                $this->form->getField('height')->isFilled(BL::err('TitleIsRequired'));
                $this->form->getField('map_type')->isFilled(BL::err('TitleIsRequired'));
                $this->form->getField('map_style')->isFilled(BL::err('TitleIsRequired'));
            }

            if ($this->form->isCorrect()) {
                // build item
                $item = [];
                $item['id'] = $this->id;
                $item['language'] = BL::getWorkingLanguage();
                $item['extra_id'] = $this->record['extra_id'];
                $item['title'] = $this->form->getField('title')->getValue();
                $item['street'] = $this->form->getField('street')->getValue();
                $item['number'] = $this->form->getField('number')->getValue();
                $item['zip'] = $this->form->getField('zip')->getValue();
                $item['city'] = $this->form->getField('city')->getValue();
                $item['country'] = $this->form->getField('country')->getValue();
                $item['override_map_settings'] = $this->form->getField('override_map_settings')->isChecked();

                // check if it's necessary to geocode again
                if ($this->record['lat'] === null || $this->record['lng'] === null || $item['street'] != $this->record['street'] || $item['number'] != $this->record['number'] || $item['zip'] != $this->record['zip'] || $item['city'] != $this->record['city'] || $item['country'] != $this->record['country']) {
                    // define coordinates
                    $coordinates = BackendModel::get(Geolocation::class)->getCoordinates(
                        $item['street'],
                        $item['number'],
                        $item['city'],
                        $item['zip'],
                        $item['country']
                    );

                    // define latitude and longitude
                    $item['lat'] = $coordinates['latitude'];
                    $item['lng'] = $coordinates['longitude'];
                } else {
                    $item['lat'] = $this->record['lat'];
                    $item['lng'] = $this->record['lng'];
                }

                // insert the item
                BackendLocationModel::update($item);

                $generalSettings = $this->get('fork.settings')->getForModule('Location');
                $center = [
                    'lat' => (float) $this->form->getField('centerLat')->getValue(),
                    'lng' => (float) $this->form->getField('centerLng')->getValue(),
                ];
                if ($this->form->getField('override_map_settings')->isChecked()) {
                    $height = (int) $this->form->getField('height')->getValue();
                    $width = (int) $this->form->getField('width')->getValue();

                    if ($width > 800) {
                        $width = 800;
                    }
                    if ($width < 300) {
                        $width = $generalSettings['width'];
                    }
                    if ($height < 150) {
                        $height = $generalSettings['height'];
                    }

                    // no id given, this means we should update the main map
                    BackendLocationModel::setMapSetting($this->id, 'zoom_level', (string) $this->form->getField('zoom_level')->getValue());
                    BackendLocationModel::setMapSetting($this->id, 'map_type', (string) $this->form->getField('map_type')->getValue());
                    BackendLocationModel::setMapSetting($this->id, 'map_style', (string) $this->form->getField('map_style')->getValue());
                    BackendLocationModel::setMapSetting($this->id, 'center', (array) $center);
                    BackendLocationModel::setMapSetting($this->id, 'height', (int) $height);
                    BackendLocationModel::setMapSetting($this->id, 'width', (int) $width);
                    BackendLocationModel::setMapSetting($this->id, 'directions', $this->form->getField('directions')->getValue());
                    BackendLocationModel::setMapSetting($this->id, 'full_url', $this->form->getField('full_url')->getValue());
                } else {
                    $center = ['lat' => (float) $this->record['lat'], 'lng' =>(float)  $this->record['lng']];
                    BackendLocationModel::setMapSetting($this->id, 'zoom_level', (string) $generalSettings['zoom_level']);
                    BackendLocationModel::setMapSetting($this->id, 'map_type', (string) $generalSettings['map_type']);
                    BackendLocationModel::setMapSetting($this->id, 'map_style', (string) 'standard');
                    BackendLocationModel::setMapSetting($this->id, 'center', $center);
                    BackendLocationModel::setMapSetting($this->id, 'height', (int)  $generalSettings['height']);
                    BackendLocationModel::setMapSetting($this->id, 'width', (int)  $generalSettings['width']);
                    BackendLocationModel::setMapSetting($this->id, 'directions', false);
                    BackendLocationModel::setMapSetting($this->id, 'full_url', false);
                }

                // redirect to the overview
                if ($this->form->getField('redirect')->getValue() == 'overview') {
                    $this->redirect(BackendModel::createUrlForAction('Index') . '&report=edited&var=' . rawurlencode($item['title']) . '&highlight=row-' . $item['id']);
                } else {
                    $this->redirect(BackendModel::createUrlForAction('Edit') . '&id=' . $item['id'] . '&report=edited');
                }
            }
        }
    }

    private function loadDeleteForm(): void
    {
        $deleteForm = $this->createForm(
            DeleteType::class,
            ['id' => $this->record['id']],
            ['module' => $this->getModule()]
        );
        $this->template->assign('deleteForm', $deleteForm->createView());
    }
}
