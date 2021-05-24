<?php

namespace ForkCMS\Modules\MediaLibrary;

use ForkCMS\Core\Backend\Domain\Config\Config as BackendBaseConfig;

/**
 * This is the configuration-object for the media library module
 */
class Config extends BackendBaseConfig
{
    /**
     * The default action
     *
     * @var string
     */
    protected $defaultAction = 'MediaItemIndex';
}
