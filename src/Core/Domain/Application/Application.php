<?php

namespace ForkCMS\Core\Domain\Application;

use Spatie\Enum\Enum;

/**
 * @method static self backend()
 * @method static self frontend()
 * @method static self console()
 * @method static self api()
 */
final class Application extends Enum
{
}
