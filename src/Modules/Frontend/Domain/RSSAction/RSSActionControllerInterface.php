<?php

declare(strict_types=1);

namespace ForkCMS\Modules\Frontend\Domain\RSSAction;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AutoconfigureTag(self::class)]
interface RSSActionControllerInterface
{
    public function __invoke(Request $request): Response;

    public static function getRSSActionSlug(): RSSActionSlug;
}
