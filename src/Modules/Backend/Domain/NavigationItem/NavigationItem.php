<?php

namespace ForkCMS\Modules\Backend\Domain\NavigationItem;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ForkCMS\Modules\Backend\Domain\Action\ActionSlug;
use ForkCMS\Modules\Backend\Domain\Action\ActionSlugDBALType;
use ForkCMS\Modules\Backend\Domain\Action\ModuleAction;
use ForkCMS\Modules\Backend\Domain\User\Blameable;
use ForkCMS\Modules\Internationalisation\Domain\Translation\TranslationKey;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: NavigationItemRepository::class)]
#[UniqueEntity(fields: ['label', 'slug', 'parent'])]
class NavigationItem
{
    use Blameable;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private(set) int $id;

    #[ORM\ManyToOne(targetEntity: NavigationItem::class, inversedBy: 'children')]
    private(set) ?self $parent;

    /**
     * @var Collection<int, NavigationItem>
     */
    #[ORM\OneToMany(targetEntity: NavigationItem::class, mappedBy: 'parent')]
    #[ORM\OrderBy(['sequence' => 'ASC'])]
    private(set) Collection $children;

    #[ORM\Embedded(class: TranslationKey::class)]
    private(set) TranslationKey $label;

    #[ORM\Column(type: ActionSlugDBALType::class, nullable: true)]
    private(set) ?ActionSlug $slug;

    #[ORM\Column(type: Types::BOOLEAN)]
    private(set) bool $visibleInNavigationMenu;

    #[ORM\Column(type: Types::INTEGER, options: ['unsigned' => true])]
    private(set) int $sequence;

    public function __construct(
        TranslationKey $label,
        ?ActionSlug $slug = null,
        ?self $parent = null,
        bool $visibleInNavigationMenu = true,
        ?int $sequence = null
    ) {
        $this->label = $label;
        $this->slug = $slug;
        $this->parent = $parent;
        $this->visibleInNavigationMenu = $visibleInNavigationMenu;
        $this->sequence = $sequence ?? $this->getFallbackSequence($parent);
        $this->children = new ArrayCollection();
        if ($parent instanceof self) {
            $parent->children->add($this);
        }
    }

    public function getModuleAction(): ?ModuleAction
    {
        if ($this->slug === null) {
            return null;
        }

        return $this->slug->asModuleAction();
    }

    private function getFallbackSequence(?self $parent = null): int
    {
        if ($parent === null) {
            throw new InvalidArgumentException('Cannot calculate next sequence, please pass a sequence as an argument');
        }

        return $parent->children->count() + 1;
    }

    public function getFirstAvailableSlug(): ActionSlug
    {
        return $this->getSlugRecursive() ?? throw new RuntimeException('No slug found');
    }

    private function getSlugRecursive(): ?ActionSlug
    {
        if ($this->slug instanceof ActionSlug) {
            return $this->slug;
        }

        foreach ($this->children as $navigationItem) {
            $slug = $navigationItem->getSlugRecursive();
            if ($slug instanceof ActionSlug) {
                return $slug;
            }
        }

        return null;
    }
}
