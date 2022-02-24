<?php

namespace ForkCMS\Core\Domain\Application;

enum Application: string
{
    case BACKEND = 'backend';
    case FRONTEND = 'frontend';
    case CONSOLE = 'console';
    case API = 'api';
}
