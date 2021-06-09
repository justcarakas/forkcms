<?php

namespace ForkCMS\Modules\Location\Backend\Actions;

use ForkCMS\Core\Backend\Domain\Action\ActionIndex as BackendBaseActionIndex;
use ForkCMS\Modules\Authentication\Backend\Domain\Authentication\Authentication as BackendAuthentication;
use ForkCMS\Core\Backend\Domain\DataGrid\DataGridDatabase as BackendDataGridDatabase;
use ForkCMS\Core\Backend\Domain\Form\Form as BackendForm;
use ForkCMS\Modules\Internationalisation\Backend\Domain\Translator\Language as BL;
use ForkCMS\Core\Backend\Helper\Model as BackendModel;
use ForkCMS\Modules\Location\Backend\Helper\Model as BackendLocationModel;
use ForkCMS\Core\Common\ModulesSettings;
use ForkCMS\Modules\Location\Frontend\Helper\Model as FrontendLocationModel;

/**
 * This is the index-action (default), it will display the overview of location items
 */
class Index extends BackendBaseActionIndex
{
    /**
     * The settings form
     *
     * @var BackendForm
     */
    protected $form;

    /**
     * @var array
     */
    protected $items = [];
    protected $settings = [];

    public function execute(): void
    {
        $this->header->addJS(FrontendLocationModel::getPathToMapStyles());
        parent::execute();

        // define Google Maps API key
        $apikey = $this->get(ModulesSettings::class)->get('Core', 'google_maps_key');

        // check Google Maps API key, otherwise redirect to settings
        if ($apikey === null) {
            $this->redirect(BackendModel::createUrlForAction('Index', 'Settings'));
        }

        $this->header->addJS(
            'https://maps.googleapis.com/maps/api/js?key=' . $apikey . '&language=' . BL::getInterfaceLanguage()
        );

        $this->loadData();

        $this->loadDataGrid();

        $this->parse();
        $this->display();
    }

    protected function loadData(): void
    {
        $this->items = BackendLocationModel::getAll();
        $this->settings = BackendLocationModel::getMapSettings(0);
        $firstMarker = current($this->items);

        // if there are no markers we reset it to the birthplace of Fork
        if ($firstMarker === false) {
            $firstMarker = ['lat' => '51.052146', 'lng' => '3.720491'];
        }

        // load the settings from the general settings
        if (empty($this->settings)) {
            $this->settings = $this->get(ModulesSettings::class)->getForModule('Location');

            $this->settings['map_type'] = $this->settings['map_type_widget'];
            $this->settings['map_style'] = $this->settings['map_style_widget'] ?? 'standard';

            $this->settings['center']['lat'] = $firstMarker['lat'];
            $this->settings['center']['lng'] = $firstMarker['lng'];
        }

        // no center point given yet, use the first occurrence
        if (!isset($this->settings['center'])) {
            $this->settings['center']['lat'] = $firstMarker['lat'];
            $this->settings['center']['lng'] = $firstMarker['lng'];
        }
    }

    private function loadDataGrid(): void
    {
        $this->dataGrid = new BackendDataGridDatabase(
            BackendLocationModel::QUERY_DATAGRID_BROWSE,
            [BL::getWorkingLanguage()]
        );
        $this->dataGrid->setColumnFunction('htmlspecialchars', ['[title]'], 'title', false);
        $this->dataGrid->setColumnFunction('htmlspecialchars', ['[address]'], 'address', false);
        $this->dataGrid->setSortingColumns(['address', 'title'], 'address');
        $this->dataGrid->setSortParameter('ASC');

        // check if this action is allowed
        if (BackendAuthentication::isAllowedAction('Edit')) {
            $this->dataGrid->setColumnURL(
                'title',
                BackendModel::createUrlForAction('Edit') . '&amp;id=[id]'
            );
            $this->dataGrid->addColumn(
                'edit',
                null,
                BL::lbl('Edit'),
                BackendModel::createUrlForAction('Edit') . '&amp;id=[id]',
                BL::lbl('Edit')
            );
        }
    }

    protected function parse(): void
    {
        parent::parse();

        $this->template->assign('dataGrid', (string) $this->dataGrid->getContent());
        $this->template->assign('godUser', BackendAuthentication::getUser()->isGod());

        // assign to template
        $this->template->assign('items', $this->items);
    }
}
