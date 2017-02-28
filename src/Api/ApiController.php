<?php

namespace Api;

use Symfony\Component\HttpFoundation\JsonResponse;

final class ApiController
{
    public function getUsersAction()
    {
        return JsonResponse::create('hello world');
    }
}
