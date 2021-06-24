<?php

namespace ForkCMS\Modules\Backend\Controller;

use Symfony\Component\HttpFoundation\Response;

final class BackendController
{
    public function __invoke(): Response
    {
        return new Response();
    }
}
