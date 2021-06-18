<?php

namespace ForkCMS\Modules\Backend\Domain\NavigationItem;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ForkCMS\Modules\Internationalisation\Domain\Translation\TranslationKey;
use InvalidArgumentException;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="NavigationItemRepository")
 * @ORM\Table(name="backend_navigation")
 * @ORM\HasLifecycleCallbacks
 */
#[UniqueEntity(fields: ['label', 'slug', 'parent'])]
class NavigationItem
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @Orm\ManyToOne(targetEntity="NavigationItem", inversedBy="children")
     * @Orm\JoinColumn(name="parent_id", referencedColumnName="id")
     */
    private ?self $parent;

    /**
     * @Orm\OneToMany(targetEntity="NavigationItem", mappedBy="parent")
     * @Orm\OrderBy({"sequence" = "ASC"})
     */
    private Collection $children;

    /**
     * @ORM\Embedded(class="ForkCMS\Modules\Internationalisation\Domain\Translation\TranslationKey")
     */
    private TranslationKey $label;

    /**
     * @ORM\Column(type="modules__backend__navigation__action_slug")
     */
    private ?ActionSlug $slug;

    /**
     * @ORM\Column(type="integer", options={"unsigned"=true})
     */
    private int $sequence;

    public function __construct(
        TranslationKey $label,
        ?ActionSlug $slug = null,
        ?self $parent = null,
        ?int $sequence = null
    ) {
        $this->label = $label;
        $this->slug = $slug;
        $this->parent = $parent;
        $this->sequence = $sequence ?? $this->getFallbackSequence($parent);
        $this->children = new ArrayCollection();
        if ($parent instanceof self) {
            $parent->children->add($this);
        }
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getParent(): NavigationItem
    {
        return $this->parent;
    }

    /** @return Collection&NavigationItem[] */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function getLabel(): TranslationKey
    {
        return $this->label;
    }

    public function getSlug(): ?ActionSlug
    {
        return $this->slug;
    }

    public function getSequence(): int
    {
        return $this->sequence;
    }

    private function getFallbackSequence(?self $parent = null): int
    {
        if ($parent === null) {
            throw new InvalidArgumentException(
                'Cannot calculate next sequence, please pass a sequence as an argument'
            );
        }

        return $parent->getChildren()->count() + 1;
    }
}
