<?php

namespace ForkCMS\Modules\Settings\Backend\Widgets;

use ForkCMS\Core\Backend\Domain\Widget\Widget as BackendBaseWidget;
use ForkCMS\Modules\Settings\Backend\Helper\Model as BackendSettingsModel;

/**
 * This widget will analyze the CMS warnings
 */
class Analyse extends BackendBaseWidget
{
    public function execute(): void
    {
        $this->setColumn('left');
        $this->setPosition(1);
        $this->parse();
        $this->display();
    }

    private function parse(): void
    {
        $warnings = BackendSettingsModel::getWarnings();

        if (!empty($warnings)) {
            $this->template->assign('warnings', $warnings);
        }
    }
}
