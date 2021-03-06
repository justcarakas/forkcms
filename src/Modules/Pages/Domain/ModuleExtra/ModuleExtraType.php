<?php

namespace ForkCMS\Modules\Pages\Domain\ModuleExtra;

use ForkCMS\Modules\Pages\Domain\PageBlock\Type;
use ForkCMS\Core\Common\Exception\InvalidModuleExtraType;
use JsonSerializable;
use Stringable;

final class ModuleExtraType implements JsonSerializable, Stringable
{
    private const BLOCK = 'block';
    private const WIDGET = 'widget';
    public const POSSIBLE_TYPES = [
        self::BLOCK,
        self::WIDGET,
    ];

    /** @var string */
    private $type;

    /**
     * @param string $type
     *
     * @throws InvalidModuleExtraType
     */
    public function __construct(string $type)
    {
        if (!in_array($type, self::POSSIBLE_TYPES, true)) {
            throw InvalidModuleExtraType::withType($type);
        }

        $this->type = $type;
    }

    public static function block(): self
    {
        return new self(self::BLOCK);
    }

    public static function widget(): self
    {
        return new self(self::WIDGET);
    }

    public function __toString(): string
    {
        return $this->type;
    }

    public function getPageBlockType(): Type
    {
        return new Type($this->type);
    }

    public function jsonSerialize(): string
    {
        return (string) $this;
    }
}
