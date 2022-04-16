<?php

namespace ForkCMS\Modules\Internationalisation\Domain\Translation\Event;

use ForkCMS\Modules\Internationalisation\Domain\Translation\Translation;
use Symfony\Contracts\EventDispatcher\Event;

final class TranslationCreatedEvent extends Event
{
    public function __construct(public readonly Translation $translation)
    {
    }
}
