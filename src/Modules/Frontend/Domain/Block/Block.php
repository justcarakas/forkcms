<?php

namespace ForkCMS\Modules\Frontend\Domain\Block;

use Assert\Assert;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ForkCMS\Core\Domain\Settings\EntityWithSettingsTrait;
use ForkCMS\Core\Domain\Settings\SettingsBag;
use ForkCMS\Modules\Backend\Domain\User\Blameable;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleName;
use ForkCMS\Modules\Internationalisation\Domain\Locale\Locale;
use ForkCMS\Modules\Internationalisation\Domain\Translation\TranslationKey;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[ORM\Entity(repositoryClass: BlockRepository::class)]
#[ORM\Table(name: 'frontend__block')]
class Block implements TranslatableInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private int $id;

    #[ORM\Column(type: 'modules__extensions__module__module_name')]
    private ModuleName $module;

    #[ORM\Column(type: 'modules__frontend__block__block_name')]
    private BlockName $blockName;

    #[ORM\Column(type: Types::STRING, enumType: Type::class)]
    #[Gedmo\SortableGroup]
    private Type $type;

    #[ORM\Embedded]
    private TranslationKey $label;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $hidden;

    #[ORM\Column(type: Types::INTEGER)]
    #[Gedmo\SortablePosition]
    private ?int $position = null;

    #[ORM\Column(type: Types::STRING, length: 5, nullable: true, enumType: Locale::class)]
    private ?Locale $locale = null;

    use EntityWithSettingsTrait;

    use Blameable;

    public function __construct(
        ModuleName $moduleName,
        BlockName $blockName,
        ?TranslationKey $label = null,
        ?SettingsBag $settings = null,
        bool $hidden = false,
        ?int $position = null
    ) {
        $this->module = $moduleName;
        $this->blockName = $blockName;
        $this->type = $blockName->getType();
        $this->settings = $settings ?? new SettingsBag();
        $this->label = $label ?? TranslationKey::label($blockName->getName());
        $this->hidden = $hidden;
        $this->position = $position;
        Assert::that($this->getFQCN())->classExists('Block class not found');
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getModule(): ModuleName
    {
        return $this->module;
    }

    public function getBlockName(): BlockName
    {
        return $this->blockName;
    }

    public function getType(): Type
    {
        return $this->type;
    }

    public function getLabel(): TranslationKey
    {
        return $this->label;
    }

    public function isHidden(): bool
    {
        return $this->hidden;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function hide(): void
    {
        $this->hidden = true;
    }

    public function show(): void
    {
        $this->hidden = false;
    }

    public function changePosition(int $position): void
    {
        $this->position = $position;
    }

    public function trans(TranslatorInterface $translator, string $locale = null): string
    {
        if (!$this->settings->has('extra_label')) {
            return $this->label->trans($translator);
        }

        if ($this->settings->has('extra_label_parameters')) {
            return vsprintf(
                $this->settings->get('extra_label'),
                $this->settings->get('extra_label_parameters')
            );
        }

        return $this->settings->get('extra_label');
    }

    public function getFQCN(): string
    {
        return 'ForkCMS\\Modules\\' . $this->module . '\\Frontend\\' . $this->type->getDirectoryName() . '\\' . $this->blockName;
    }

    public function __toString(): string
    {
        return $this->getFQCN();
    }
}
