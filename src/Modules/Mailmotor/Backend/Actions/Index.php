<?php

namespace ForkCMS\Modules\Mailmotor\Backend\Actions;

use ForkCMS\Core\Backend\Domain\Action\ActionIndex;
use ForkCMS\Core\Backend\Helper\Model;

/**
 * This redirects to settings
 */
final class Index extends ActionIndex
{
    public function execute(): void
    {
        parent::execute();

        $this->redirect(
            Model::createUrlForAction(
                'Settings'
            )
        );
    }
}
