<?php

namespace ForkCMS\Modules\Settings\Backend\Ajax;

use ForkCMS\Core\Backend\Domain\Ajax\AjaxAction;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\HttpFoundation\Response;

class ClearCache extends AjaxAction
{
    public function execute(): void
    {
        parent::execute();

        $kernel = $this->getKernel();
        $application = new Application($kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput(
            [
                'command' => 'forkcms:cache:clear',
            ]
        );

        $exitCode = $application->run($input);

        $this->output(
            Response::HTTP_OK,
            [
                'exitCode' => $exitCode,
            ]
        );
    }
}
