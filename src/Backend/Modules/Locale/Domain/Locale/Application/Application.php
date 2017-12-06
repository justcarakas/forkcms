<?php

namespace Backend\Modules\Locale\Domain\Locale\Application;

final class Application
{
    private const FRONTEND = 'Frontend';
    private const BACKEND = 'Backend';
    public const POSSIBLE_VALUES = [
        self::FRONTEND,
        self::BACKEND,
    ];

    /** @var string */
    private $application;

    public function __construct(string $application)
    {
        if (!\in_array($application, self::POSSIBLE_VALUES, true)) {
            throw InvalidApplicationException::notSupported($application);
        }

        $this->application = $application;
    }

    public function getValue(): string
    {
        return $this->application;
    }

    public function __toString(): string
    {
        return $this->application;
    }

    public function equals(self $application): bool
    {
        return $application->application === $this->application;
    }

    public static function backend(): self
    {
        return new self(self::BACKEND);
    }

    public function isBackend(): bool
    {
        return $this->equals(self::backend());
    }

    public static function frontend(): self
    {
        return new self(self::FRONTEND);
    }

    public function isFrontend(): bool
    {
        return $this->equals(self::frontend());
    }
}
