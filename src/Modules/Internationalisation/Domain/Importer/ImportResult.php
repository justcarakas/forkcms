<?php

namespace ForkCMS\Modules\Internationalisation\Domain\Importer;

use ForkCMS\Modules\Internationalisation\Domain\Translation\Translation;

final class ImportResult
{
    private int $successCount = 0;

    /** @var Translation[] */
    private array $failed = [];

    public function getSuccessCount(): int
    {
        return $this->successCount;
    }

    public function getFailedCount(): int
    {
        return count($this->failed);
    }

    /** @return Translation[] */
    public function getFailed(): array
    {
        return $this->failed;
    }

    public function addFailed(Translation $translation): void
    {
        $this->failed[] = $translation;
    }

    public function addSuccess(Translation $translation): void
    {
        ++$this->successCount;
    }
}
