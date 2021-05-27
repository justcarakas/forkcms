<?php

namespace ForkCMS\Core\Domain\Locale;

use Serializable;
use Spatie\Enum\Enum;

/**
 * @method static self en()
 * @method static self zh()
 * @method static self nl()
 * @method static self fr()
 * @method static self de()
 * @method static self el()
 * @method static self hu()
 * @method static self it()
 * @method static self lt()
 * @method static self ru()
 * @method static self es()
 * @method static self sv()
 * @method static self uk()
 * @method static self pl()
 * @method static self pt()
 */
final class Locale extends Enum implements Serializable
{
    protected static function labels(): array
    {
        return [
            'en' => 'English',
            'zh' => 'Chinese',
            'nl' => 'Dutch',
            'fr' => 'French',
            'de' => 'German',
            'el' => 'Greek',
            'hu' => 'Hungarian',
            'it' => 'Italian',
            'lt' => 'Lithuanian',
            'ru' => 'Russian',
            'es' => 'Spanish',
            'sv' => 'Swedish',
            'uk' => 'Ukrainian',
            'pl' => 'Polish',
            'pt' => 'Portuguese',
        ];
    }

    public function serialize(): string
    {
        return $this->value;
    }

    public function unserialize($serialized): void
    {
        $this->value = self::from($serialized)->value;
    }

    public function asTranslatable(): string
    {
        return 'lbl.' . mb_strtoupper($this->value);
    }
}
