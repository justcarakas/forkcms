<?php

namespace ForkCMS\Modules\Extensions\Backend\Actions;

use ForkCMS\Core\Backend\Domain\Action\ActionEdit;
use ForkCMS\Core\Backend\Helper\Model as BackendModel;
use ForkCMS\Modules\Extensions\Backend\Helper\Model;
use ForkCMS\Modules\Extensions\Domain\ModuleSetting\ModuleSettingRepository;
use Symfony\Component\HttpFoundation\Response;

/**
 * Exports templates in the selected theme, for ease when packaging themes.
 */
class ExportThemeTemplates extends ActionEdit
{
    /**
     * All available themes
     *
     * @var array
     */
    private $availableThemes;

    /**
     * The current selected theme
     *
     * @var string
     */
    private $selectedTheme;

    /**
     * Load the selected theme, falling back to default if none specified.
     */
    public function execute(): void
    {
        // get data
        $this->selectedTheme = $this->getRequest()->query->get('theme');

        // build available themes
        foreach (Model::getThemes() as $theme) {
            $this->availableThemes[$theme['value']] = $theme['label'];
        }

        // determine selected theme, based upon submitted form or default theme
        if (!array_key_exists($this->selectedTheme, $this->availableThemes)) {
            $this->selectedTheme = $this->get(ModuleSettingRepository::class)->get('Core', 'theme', 'Fork');
        }
    }

    public function getContent(): Response
    {
        $filename = 'templates_' . BackendModel::getUTCDate('d-m-Y') . '.xml';

        return new Response(
            Model::createTemplateXmlForExport($this->selectedTheme),
            Response::HTTP_OK,
            [
                'Content-type' => 'text/xml',
                'Content-disposition' => 'attachment; filename="' . $filename . '"',
            ]
        );
    }
}
