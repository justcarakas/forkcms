<?php

namespace ForkCMS\Modules\Pages\Domain\Revision;

enum MenuType: string
{
    case ROOT = 'root';
    case PAGE = 'page';
    case FOOTER = 'footer';
    case META = 'meta';
}
