<?php

namespace ForkCMS\Modules\Frontend\Domain\Meta;

use Spatie\Enum\Enum;
use Stringable;

/**
 * @method static self none()
 * @method static self index()
 * @method static self noindex()
 * @method bool isNone()
 * @method bool isIndex()
 * @method bool isNoindex()
 */
final class SEOIndex extends Enum implements Stringable
{
}
