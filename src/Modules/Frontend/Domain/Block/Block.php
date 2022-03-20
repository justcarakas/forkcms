<?php

namespace ForkCMS\Modules\Frontend\Domain\Block;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ForkCMS\Core\Domain\Identifier\BlockName;
use ForkCMS\Core\Domain\Settings\EntityWithSettingsTrait;
use ForkCMS\Modules\Backend\Domain\User\Blameable;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleName;
use ForkCMS\Modules\Frontend\Domain\Action\ActionName;
use ForkCMS\Modules\Frontend\Domain\Widget\WidgetName;
use ForkCMS\Modules\Internationalisation\Domain\Translation\TranslationKey;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Contracts\Translation\TranslatorInterface;

#[ORM\Entity(repositoryClass: BlockRepository::class)]
#[ORM\Table(name: 'frontend__block')]
class Block
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private int $id;

    #[ORM\Column(type: 'modules__extensions__module__module_name')]
    private ModuleName $module;

    #[ORM\Column(type: 'modules__frontend__block__block_name')]
    private ActionName|WidgetName $blockName;

    #[ORM\Column(type: Types::STRING, enumType: Type::class)]
    #[Gedmo\SortableGroup]
    private Type $type;

    private TranslationKey $label;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $hidden;

    #[ORM\Column(type: Types::INTEGER)]
    #[Gedmo\SortablePosition]
    private int $position;

    use EntityWithSettingsTrait;

    use Blameable;

    public function getId(): int
    {
        return $this->id;
    }

    public function getModule(): ModuleName
    {
        return $this->module;
    }

    public function getBlockName(): ActionName|WidgetName
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

    public function getTranlsatedLabel(TranslatorInterface $translator): string
    {
        if (!$this->settings->has('extra_label')) {
            return $this->label->trans($translator);
        }

        if ($this->settings->has('extra_label_variables')) {
            return vsprintf($this->settings->get('extra_label'), $this->settings->get('extra_label_parameters'));
        }

        return $this->settings->get('extra_label');
    }
}
