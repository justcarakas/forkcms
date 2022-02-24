<?php

namespace ForkCMS\Modules\Internationalisation\Domain\Translation;

enum Type: string
{
    case msg = 'message';
    case lbl = 'label';
    case slg = 'slug';
    case err = 'error';
}
