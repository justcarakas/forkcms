<?php

namespace ForkCMS\Modules\Extensions\Backend;

use ForkCMS\Core\Backend\Domain\Config\Config as BackendBaseConfig;

/**
 * This is the configuration-object for the extensions module.
 */
final class Config extends BackendBaseConfig
{
    /**
     * The default action
     *
     * @var string
     */
    protected $defaultAction = 'Modules';
}
