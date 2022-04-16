<?php

namespace ForkCMS\Modules\Internationalisation\Domain\Translation\Event;

use ForkCMS\Modules\Internationalisation\Domain\Translation\Translation;
use Symfony\Contracts\EventDispatcher\Event;

final class TranslationChangedEvent extends Event
{
    public function __construct(public readonly Translation $translation)
    {
    }
}
