<?php

namespace ForkCMS\Modules\Extensions\Backend\Actions;

use ForkCMS\Core\Backend\Domain\Action\ActionEdit as BackendBaseActionEdit;
use ForkCMS\Modules\Authentication\Backend\Domain\Authentication\Authentication as BackendAuthentication;
use ForkCMS\Core\Backend\Helper\Model as BackendModel;
use ForkCMS\Core\Backend\Domain\Form\Form as BackendForm;
use ForkCMS\Core\Backend\Domain\DataGrid\DataGridDatabase as BackendDataGridDatabase;
use ForkCMS\Modules\Internationalisation\Backend\Domain\Translator\Language as BL;
use ForkCMS\Modules\Extensions\Backend\Helper\Model as BackendExtensionsModel;
use ForkCMS\Modules\Extensions\Domain\ModuleSetting\ModuleSettingRepository;

/**
 * This is the templates-action, it will display the templates-overview
 */
class ThemeTemplates extends BackendBaseActionEdit
{
    /**
     * All available themes
     *
     * @var array
     */
    private $availableThemes;

    /**
     * @var BackendDataGridDatabase
     */
    private $dataGrid;

    /**
     * The current selected theme
     *
     * @var string
     */
    private $selectedTheme;

    public function execute(): void
    {
        parent::execute();
        $this->loadData();
        $this->loadForm();
        $this->loadDataGrid();
        $this->parse();
        $this->display();
    }

    private function loadData(): void
    {
        // get data
        $this->selectedTheme = $this->getRequest()->query->get('theme');

        // build available themes
        foreach (BackendExtensionsModel::getThemes() as $theme) {
            $this->availableThemes[$theme['value']] = $theme['label'];
        }

        // determine selected theme, based upon submitted form or default theme
        if (!array_key_exists($this->selectedTheme, $this->availableThemes)) {
            $this->selectedTheme = $this->get(ModuleSettingRepository::class)->get('Core', 'theme', 'Fork');
        }
    }

    private function loadDataGrid(): void
    {
        // create datagrid
        $this->dataGrid = new BackendDataGridDatabase(
            BackendExtensionsModel::QUERY_BROWSE_TEMPLATES,
            [$this->selectedTheme]
        );

        $this->dataGrid->setColumnFunction('htmlspecialchars', ['[title]'], 'title', false);

        // check if this action is allowed
        if (BackendAuthentication::isAllowedAction('EditThemeTemplate')) {
            // set colum URLs
            $this->dataGrid->setColumnURL(
                'title',
                BackendModel::createUrlForAction('EditThemeTemplate') . '&amp;id=[id]'
            );

            // add edit column
            $this->dataGrid->addColumn(
                'edit',
                null,
                BL::lbl('Edit'),
                BackendModel::createUrlForAction('EditThemeTemplate') . '&amp;id=[id]',
                BL::lbl('Edit')
            );
        }
    }

    private function loadForm(): void
    {
        // create form
        $this->form = new BackendForm('themes');

        // create elements
        $this->form->addDropdown(
            'theme',
            $this->availableThemes,
            $this->selectedTheme,
            false,
            'form-control dontCheckBeforeUnload',
            'form-control dontCheckBeforeUnload'
        );
    }

    protected function parse(): void
    {
        parent::parse();

        // assign datagrid
        $this->template->assign('dataGrid', $this->dataGrid->getContent());

        // assign the selected theme, so we can propagate it to the add/edit actions.
        $this->template->assign('selectedTheme', rawurlencode($this->selectedTheme));
    }
}
