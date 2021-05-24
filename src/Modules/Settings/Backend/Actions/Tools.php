<?php

namespace ForkCMS\Modules\Settings\Backend\Actions;

use ForkCMS\Core\Backend\Domain\Action\Action;

class Tools extends Action
{
    public function execute(): void
    {
        parent::execute();

        $this->display();
    }
}
