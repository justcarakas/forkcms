<?php

namespace ForkCMS\Modules\Backend\Domain\Widget;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface WidgetControllerInterface
{
    public function __invoke(Request $request): string;
}
