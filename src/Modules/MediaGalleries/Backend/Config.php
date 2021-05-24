<?php

namespace ForkCMS\Modules\MediaGalleries\Backend;

use ForkCMS\Core\Backend\Domain\Config\Config as BackendBaseConfig;

/**
 * This is the configuration-object for the media galleries module
 */
class Config extends BackendBaseConfig
{
    /**
     * The default action
     *
     * @var string
     */
    protected $defaultAction = 'MediaGalleryIndex';
}
