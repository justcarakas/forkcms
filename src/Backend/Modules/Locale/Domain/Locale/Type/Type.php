<?php

namespace Backend\Modules\Locale\Domain\Locale\Type;

final class Type
{
    private const LABEL = 'lbl';
    private const MESSAGE = 'msg';
    private const ACTION = 'act';
    private const ERROR = 'err';
    public const POSSIBLE_VALUES = [
        self::LABEL,
        self::MESSAGE,
        self::ACTION,
        self::ERROR,
    ];

    /** @var string */
    private $type;

    public function __construct(string $type)
    {
        if (!\in_array($type, self::POSSIBLE_VALUES, true)) {
            throw InvalidTypeException::notSupported($type);
        }

        $this->type = $type;
    }

    public function getValue(): string
    {
        return $this->type;
    }

    public function __toString(): string
    {
        return $this->type;
    }

    public function equals(self $type): bool
    {
        return $type->type === $this->type;
    }

    public static function label(): self
    {
        return new self(self::LABEL);
    }

    public function isLabel(): bool
    {
        return $this->equals(self::label());
    }

    public static function message(): self
    {
        return new self(self::MESSAGE);
    }

    public function isMessage(): bool
    {
        return $this->equals(self::message());
    }

    public static function action(): self
    {
        return new self(self::ACTION);
    }

    public function isAction(): bool
    {
        return $this->equals(self::action());
    }

    public static function error(): self
    {
        return new self(self::ERROR);
    }

    public function isError(): bool
    {
        return $this->equals(self::error());
    }
}
