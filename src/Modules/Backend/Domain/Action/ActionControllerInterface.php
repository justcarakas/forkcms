<?php

namespace ForkCMS\Modules\Backend\Domain\Action;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AutoconfigureTag(self::class)]
interface ActionControllerInterface
{
    public function __invoke(Request $request): Response;

    public static function getActionSlug(): ActionSlug;
}
