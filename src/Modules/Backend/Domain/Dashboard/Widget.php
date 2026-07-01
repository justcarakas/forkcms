<?php

declare(strict_types=1);

namespace ForkCMS\Modules\Backend\Domain\Dashboard;

use Symfony\Contracts\Translation\TranslatableInterface;

final readonly class Widget
{
    public function __construct(
        public TranslatableInterface|string $moduleLabel,
        public TranslatableInterface|string $widgetLabel,
        public string $content
    ) {
    }
}
