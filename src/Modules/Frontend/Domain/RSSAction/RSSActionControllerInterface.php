<?php

namespace ForkCMS\Modules\Frontend\Domain\RSSAction;

use ForkCMS\Modules\Frontend\Domain\RSSAction\RSSActionSlug;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface RSSActionControllerInterface
{
    public function __invoke(Request $request): Response;

    public static function getRSSActionSlug(): RSSActionSlug;
}
