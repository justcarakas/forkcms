<?php

namespace ForkCMS\Modules\Pages\Backend;

use ForkCMS\Core\Backend\Domain\Config\Config as BackendBaseConfig;

/**
 * This is the configuration-object for the pages module
 */
class Config extends BackendBaseConfig
{
    /**
     * @var string
     */
    protected $defaultAction = 'PageIndex';
}
