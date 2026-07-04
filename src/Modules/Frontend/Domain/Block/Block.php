<?php

declare(strict_types=1);

namespace ForkCMS\Modules\Frontend\Domain\Block;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ForkCMS\Core\Domain\Settings\EntityWithSettingsTrait;
use ForkCMS\Core\Domain\Settings\SettingsBag;
use ForkCMS\Modules\Backend\Domain\User\Blameable;
use ForkCMS\Modules\Internationalisation\Domain\Locale\Locale;
use ForkCMS\Modules\Internationalisation\Domain\Translation\TranslationKey;
use Gedmo\Mapping\Annotation as Gedmo;
use Stringable;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[ORM\Entity(repositoryClass: BlockRepository::class)]
class Block implements TranslatableInterface, Stringable
{
    use EntityWithSettingsTrait;
    use Blameable;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private(set) int $id;

    #[ORM\Embedded]
    private(set) ModuleBlock $block;

    #[ORM\Column(type: Types::STRING, enumType: Type::class)]
    #[Gedmo\SortableGroup]
    private(set) Type $type;

    #[ORM\Embedded]
    private(set) TranslationKey $label;

    #[ORM\Column(type: Types::BOOLEAN)]
    private(set) bool $enabled;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    #[Gedmo\SortablePosition]
    private(set) ?int $position;

    #[ORM\Column(type: Types::STRING, length: 5, nullable: true, enumType: Locale::class)]
    private(set) ?Locale $locale = null;

    public function __construct(
        ModuleBlock $block,
        ?TranslationKey $label = null,
        ?SettingsBag $settings = null,
        bool $enabled = true,
        ?int $position = null,
        ?Locale $locale = null,
    ) {
        $this->block = $block;
        $this->type = $block->name->getType();
        $this->settings = $settings ?? new SettingsBag();
        $this->label = $label ?? $block->name->asLabel();
        $this->enabled = $enabled;
        $this->position = $position;
        $this->locale = $locale;
    }

    public function getSettings(): SettingsBag
    {
        return $this->settings;
    }

    public function disable(): void
    {
        $this->enabled = false;
    }

    public function enable(): void
    {
        $this->enabled = true;
    }

    public function changePosition(int $position): void
    {
        $this->position = $position;
    }

    #[\Override]
    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        $module = $this->block->module->asLabel()->trans($translator) . ' › ';
        $hasOverwrite = $this->settings->has('label');
        $hasLocaleSpecificOverwrite = $this->settings->has('label_' . $locale);
        if (!$hasOverwrite && !$hasLocaleSpecificOverwrite) {
            return $module . $this->label->trans($translator);
        }

        $overwriteSettingName = $hasLocaleSpecificOverwrite ? 'label_' . $locale : 'label';

        if ($this->settings->has($overwriteSettingName . '_parameters')) {
            return $module . vsprintf(
                $this->settings->get($overwriteSettingName),
                $this->settings->get($overwriteSettingName . '_parameters')
            );
        }

        return $module . $this->settings->get($overwriteSettingName);
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->block->getFQCN();
    }
}
