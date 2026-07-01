<?php

declare(strict_types=1);

namespace ForkCMS\Core\Domain\Application;

use ForkCMS\Core\Domain\Enum\TryFromNullable;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum Application: string implements TranslatableInterface
{
    use TryFromNullable;

    case BACKEND = 'backend';
    case FRONTEND = 'frontend';
    case INSTALLER = 'installer';
    case CONSOLE = 'console';
    case API = 'api';

    #[\Override]
    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans('lbl.Application' . ucfirst($this->value), locale: $locale);
    }

    public function hasEditableTranslations(): bool
    {
        return match ($this) {
            self::BACKEND, self::FRONTEND, self::CONSOLE => true,
            default => false,
        };
    }
}
