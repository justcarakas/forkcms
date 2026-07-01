<?php

declare(strict_types=1);

namespace ForkCMS\Modules\Internationalisation\Domain\Translation\Command;

final readonly class DeleteTranslation
{
    public function __construct(public string $translationId)
    {
    }
}
