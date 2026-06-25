<?php

namespace ForkCMS\Modules\Internationalisation\Domain\Exporter;

use ForkCMS\Core\Domain\Util\Ensure;
use ForkCMS\Modules\Internationalisation\Domain\Translation\Translation;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\DependencyInjection\ServiceLocator;

final class Exporter
{
    /** @param ServiceLocator<ExporterInterface> $exporters */
    public function __construct(
        #[AutowireLocator('forkcms.translation.exporter', defaultIndexMethod: 'forExtension')]
        private readonly ServiceLocator $exporters
    ) {
    }

    /** @param iterable<Translation> $translations */
    public function export(iterable $translations, string $extension): string
    {
        $exporter = Ensure::isImplementingInterface($this->exporters->get($extension), ExporterInterface::class);

        return $exporter->exportTranslations($translations);
    }

    /** @return string[] */
    public function getAvailableExtensions(): array
    {
        return array_keys($this->exporters->getProvidedServices());
    }
}
