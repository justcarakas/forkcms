<?php

namespace ForkCMS\Core\Backend;

use SpoonFilter;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * This class will initiate the backend-application
 */
class Init extends \ForkCMS\Core\Common\Init
{
    public function __construct(KernelInterface $kernel)
    {
        $this->allowedTypes = ['Backend', 'BackendAjax', 'Console'];

        parent::__construct($kernel);
    }
}
