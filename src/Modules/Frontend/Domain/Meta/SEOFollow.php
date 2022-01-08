<?php

namespace ForkCMS\Modules\Frontend\Domain\Meta;

use Spatie\Enum\Enum;
use Stringable;

/**
 * @method static self none()
 * @method static self follow()
 * @method static self nofollow()
 * @method bool isNone()
 * @method bool isFollow()
 * @method bool isNofollow()
 */
final class SEOFollow extends Enum implements Stringable
{
}
