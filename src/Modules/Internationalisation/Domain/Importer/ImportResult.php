<?php

namespace ForkCMS\Modules\Internationalisation\Domain\Importer;

use ForkCMS\Modules\Internationalisation\Domain\Translation\Translation;

final class ImportResult
{
    private int $importedCount = 0;
    private int $updatedCount = 0;

    /** @var Translation[] */
    private array $failed = [];

    public function getImportedCount(): int
    {
        return $this->importedCount;
    }

    public function getUpdatedCount(): int
    {
        return $this->updatedCount;
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

    public function addImported(Translation $translation): void
    {
        ++$this->importedCount;
    }

    public function addUpdated(Translation $translation): void
    {
        ++$this->updatedCount;
    }
}
