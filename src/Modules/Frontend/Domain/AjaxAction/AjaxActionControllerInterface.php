<?php

namespace ForkCMS\Modules\Frontend\Domain\AjaxAction;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AutoconfigureTag('forkcms.frontend.ajax_action')]
interface AjaxActionControllerInterface
{
    public function __invoke(Request $request): Response;

    public static function getAjaxActionSlug(): AjaxActionSlug;
}
