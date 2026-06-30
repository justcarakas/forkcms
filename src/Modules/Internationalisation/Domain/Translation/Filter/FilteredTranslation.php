<?php

namespace ForkCMS\Modules\Internationalisation\Domain\Translation\Filter;

use ForkCMS\Core\Domain\Application\Application;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleName;
use ForkCMS\Modules\Internationalisation\Domain\Locale\Locale;
use ForkCMS\Modules\Internationalisation\Domain\Translation\Translation;
use ForkCMS\Modules\Internationalisation\Domain\Translation\Type;
use Pageon\DoctrineDataGridBundle\Attribute\DataGrid;

#[DataGrid('translation', noResultsMessage: 'msg.NoItemsFilter')]
final class FilteredTranslation
{
    /** @var array<string, string> */
    private array $values;
    /** @var array<string, string> */
    private array $ids;

    private function __construct(
        public readonly Application $application,
        public readonly ModuleName $moduleName,
        public readonly string $name,
        public readonly Type $type
    ) {
    }

    public static function forTranslation(Translation $translation): self
    {
        return new self(
            $translation->domain->application,
            $translation->domain->moduleName ?? ModuleName::core(),
            $translation->key,
            $translation->key->type,
        );
    }

    public function addTranslation(Translation $translation): void
    {
        $this->values[$translation->locale->value] = $translation->value;
        $this->ids[$translation->locale->value] = $translation->id;
    }

    public function getValue(Locale $locale): string
    {
        return $this->values[$locale->value] ?? '';
    }

    public function getId(Locale $locale): ?string
    {
        return $this->ids[$locale->value] ?? null;
    }

    /** @param null[] $arguments */
    public function __call(string $locale, array $arguments): string
    {
        return $this->getValue(Locale::from($locale));
    }
}
