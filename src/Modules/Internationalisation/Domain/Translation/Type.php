<?php

namespace ForkCMS\Modules\Internationalisation\Domain\Translation;

use Spatie\Enum\Enum;

/**
 * @method static self message()
 * @method static self label()
 * @method static self slug()
 * @method static self error()
 */
final class Type extends Enum
{
    protected static function values(): array
    {
        return [
            'message' => 'msg',
            'label' => 'lbl',
            'slug' => 'slg',
            'error' => 'err',
        ];
    }
}
