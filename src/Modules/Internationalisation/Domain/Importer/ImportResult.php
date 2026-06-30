<?php

namespace ForkCMS\Modules\Internationalisation\Domain\Importer;

use ForkCMS\Modules\Internationalisation\Domain\Translation\Translation;

final class ImportResult
{
    private(set) int $importedCount = 0;
    private(set) int $updatedCount = 0;
    private(set) int $skippedCount = 0;
    /** @var Translation[] */
    private(set) array $failed = [];

    public function getFailedCount(): int
    {
        return count($this->failed);
    }

    public function addFailed(Translation $translation): void
    {
        $this->failed[] = $translation;
    }

    public function addImported(): void
    {
        ++$this->importedCount;
    }

    public function addUpdated(): void
    {
        ++$this->updatedCount;
    }

    public function addSkipped(): void
    {
        ++$this->skippedCount;
    }

    public function getTotalCount(): int
    {
        return $this->skippedCount
               + $this->updatedCount
               + $this->getFailedCount()
               + $this->importedCount;
    }
}
