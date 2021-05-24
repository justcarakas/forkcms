<?php

namespace ForkCMS\Modules\Profiles\Backend\Actions;

use ForkCMS\Modules\Authentication\Backend\Domain\Authentication\Authentication;
use ForkCMS\Core\Backend\Domain\Action\ActionAdd as BackendBaseActionAdd;
use ForkCMS\Core\Common\Exception\RedirectException;
use ForkCMS\Core\Common\ForkCMS\Utility\Csv\Writer;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

/**
 * This is the add-action, it will display a form to add a new profile.
 */
class ExportTemplate extends BackendBaseActionAdd
{
    public function execute(): void
    {
        $this->checkToken();

        $spreadSheet = new Spreadsheet();
        $sheet = $spreadSheet->getActiveSheet();
        $sheet->fromArray(
            [
                'email',
                'display_name',
                'password',
            ],
            null,
            'A1'
        );

        throw new RedirectException(
            'Return the csv data',
            $this->get(Writer::class)
                ->getResponseForUser(
                    $spreadSheet,
                    'import_template.csv',
                    Authentication::getUser()
                )
        );
    }
}
