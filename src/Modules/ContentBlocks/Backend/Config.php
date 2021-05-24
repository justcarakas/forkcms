<?php

namespace ForkCMS\Modules\ContentBlocks\Backend;

use ForkCMS\Core\Backend\Domain\Config\Config as BackendBaseConfig;

/**
 * This is the configuration-object for the content_blocks module
 */
class Config extends BackendBaseConfig
{
    /**
     * The default action
     *
     * @var string
     */
    protected $defaultAction = 'Index';
}
