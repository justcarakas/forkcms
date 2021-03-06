<?php

namespace ForkCMS\Modules\Location\Backend\Actions;

use ForkCMS\Core\Backend\Domain\Action\ActionEdit as BackendBaseActionEdit;
use ForkCMS\Modules\Authentication\Backend\Domain\Authentication\Authentication as BackendAuthentication;
use ForkCMS\Core\Backend\Domain\Form\Form as BackendForm;
use ForkCMS\Modules\Internationalisation\Backend\Domain\Translator\Language as BL;
use ForkCMS\Core\Backend\Helper\Model as BackendModel;
use ForkCMS\Modules\Extensions\Domain\ModuleSetting\ModuleSettingRepository;
use ForkCMS\Modules\Location\Backend\Helper\Model as BackendLocationModel;

/**
 * This is the settings-action, it will display a form to set general location settings
 */
class Settings extends BackendBaseActionEdit
{
    public function execute(): void
    {
        parent::execute();
        $this->loadForm();
        $this->validateForm();
        $this->parse();
        $this->display();
    }

    private function loadForm(): void
    {
        $this->form = new BackendForm('settings');

        // add map info (widgets)
        $this->form->addDropdown('zoom_level_widget', array_combine(array_merge(['auto'], range(3, 18)), array_merge([BL::lbl('Auto', $this->getModule())], range(3, 18))), $this->get(ModuleSettingRepository::class)->get($this->url->getModule(), 'zoom_level_widget', 13));
        $this->form->addText('width_widget', $this->get(ModuleSettingRepository::class)->get($this->url->getModule(), 'width_widget'));
        $this->form->addText('height_widget', $this->get(ModuleSettingRepository::class)->get($this->url->getModule(), 'height_widget'));
        $this->form->addDropdown(
            'map_type_widget',
            [
                'ROADMAP' => BL::lbl('Roadmap', $this->getModule()),
                'SATELLITE' => BL::lbl('Satellite', $this->getModule()),
                'HYBRID' => BL::lbl('Hybrid', $this->getModule()),
                'TERRAIN' => BL::lbl('Terrain', $this->getModule()),
                'STREET_VIEW' => BL::lbl('StreetView', $this->getModule()),
            ],
            $this->get(ModuleSettingRepository::class)->get(
                $this->url->getModule(),
                'map_type_widget',
                'roadmap'
            )
        );
    }

    protected function parse(): void
    {
        parent::parse();
        $this->template->assign('godUser', BackendAuthentication::getUser()->isGod());
    }

    private function validateForm(): void
    {
        if ($this->form->isSubmitted()) {
            $this->form->cleanupFields();

            if ($this->form->isCorrect()) {
                // set the base values
                $width = (int) $this->form->getField('width_widget')->getValue();
                $height = (int) $this->form->getField('height_widget')->getValue();

                if ($width > 800) {
                    $width = 800;
                } elseif ($width < 300) {
                    $width = $this->get(ModuleSettingRepository::class)->get('Location', 'width_widget');
                }
                if ($height < 150) {
                    $height = $this->get(ModuleSettingRepository::class)->get('Location', 'height_widget');
                }

                // set our settings (widgets)
                $this->get(ModuleSettingRepository::class)->set($this->url->getModule(), 'zoom_level_widget', (string) $this->form->getField('zoom_level_widget')->getValue());
                $this->get(ModuleSettingRepository::class)->set($this->url->getModule(), 'width_widget', $width);
                $this->get(ModuleSettingRepository::class)->set($this->url->getModule(), 'height_widget', $height);
                $this->get(ModuleSettingRepository::class)->set($this->url->getModule(), 'map_type_widget', (string) $this->form->getField('map_type_widget')->getValue());

                $locations = BackendLocationModel::getAllWithDefaultMapSettings();
                foreach ($locations as $location) {
                    BackendLocationModel::setMapSetting($location['id'], 'zoom_level', (string) $this->form->getField('zoom_level_widget')->getValue());
                    BackendLocationModel::setMapSetting($location['id'], 'map_type', (string) $this->form->getField('map_type_widget')->getValue());
                    BackendLocationModel::setMapSetting($location['id'], 'height', $height);
                    BackendLocationModel::setMapSetting($location['id'], 'width', $width);
                }

                // redirect to the settings page
                $this->redirect(BackendModel::createUrlForAction('Settings') . '&report=saved');
            }
        }
    }
}
