<?php

namespace ForkCMS\Modules\Backend\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;

final class BackendAjaxController
{
    public function __invoke(): JsonResponse
    {
        return new JsonResponse();
    }
}
