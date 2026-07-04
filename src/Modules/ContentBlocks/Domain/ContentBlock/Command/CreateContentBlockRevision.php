<?php

declare(strict_types=1);

namespace ForkCMS\Modules\ContentBlocks\Domain\ContentBlock\Command;

use ForkCMS\Modules\ContentBlocks\Domain\ContentBlock\ContentBlockDataTransferObject;
use ForkCMS\Modules\ContentBlocks\Domain\ContentBlock\Revision;
use ForkCMS\Modules\Internationalisation\Domain\Locale\Locale;

final class CreateContentBlockRevision extends ContentBlockDataTransferObject
{
    private function __construct(?Revision $revisionEntity = null, public readonly ?Locale $locale = null)
    {
        parent::__construct($revisionEntity);
    }

    public static function new(Locale $locale): self
    {
        return new self(null, $locale);
    }

    public static function fromRevision(Revision $revision): self
    {
        return new self($revision);
    }

    public function setEntity(Revision $revision): void
    {
        $this->revisionEntity = $revision;
    }
}
