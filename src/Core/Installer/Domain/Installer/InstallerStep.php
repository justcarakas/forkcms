<?php

namespace ForkCMS\Core\Installer\Domain\Installer;

use Closure;
use InvalidArgumentException;
use Spatie\Enum\Enum;

/**
 * @method static self requirements()
 * @method static self locales()
 * @method static self modules()
 * @method static self database()
 * @method static self login()
 * @method static self install()
 */
final class InstallerStep extends Enum
{
    protected static function values(): array
    {
        return [
            'requirements' => 1,
            'locales' => 2,
            'modules' => 3,
            'database' => 4,
            'login' => 5,
            'install' => 6,
        ];
    }

    protected static function labels(): array
    {
        return self::values();
    }

    public function next(): self
    {
        return self::from($this->value + 1);
    }

    public function previous(): self
    {
        return self::from($this->value - 1);
    }

    public function hasPrevious(): bool
    {
        return self::tryFrom($this->value - 1) !== null;
    }

    public function route(): string
    {
        return 'install_step' . $this->value;
    }

    public function template(): string
    {
        return 'step' . $this->value . '.html.twig';
    }
}
