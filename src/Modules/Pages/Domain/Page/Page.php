<?php

namespace ForkCMS\Modules\Pages\Domain\Page;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ForkCMS\Core\Domain\Settings\EntityWithSettingsTrait;
use ForkCMS\Core\Domain\Settings\SettingsBag;
use ForkCMS\Modules\Internationalisation\Domain\Locale\Locale;
use ForkCMS\Modules\Pages\Domain\Revision\Revision;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[ORM\Entity(repositoryClass: PageRepository::class)]
#[ORM\Table(name: 'pages__page')]
class Page
{
    public const PAGE_ID_HOME = 1;
    public const PAGE_ID_404 = 404;
    public const PAGE_ID_START = 1000;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private int $id;

    #[ORM\Column(type: 'string', length: 5, enumType: Locale::class)]
    private Locale $originalLocale;

    #[ORM\OneToMany(mappedBy: 'page', targetEntity: Revision::class, cascade: ['persist', 'remove'])]
    private Collection $revisions;

    #[ORM\OneToMany(mappedBy: 'parentPage', targetEntity: Revision::class)]
    private Collection $childRevisions;

    use EntityWithSettingsTrait;

    public function __construct(Locale $originalLocale)
    {
        $this->originalLocale = $originalLocale;
        $this->revisions = new ArrayCollection();
        $this->childRevisions = new ArrayCollection();
        $this->settings = new SettingsBag();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function hasId(): bool
    {
        return isset($this->id);
    }

    public function getOriginalLocale(): Locale
    {
        return $this->originalLocale;
    }

    public function getRevisions(): Collection
    {
        return $this->revisions;
    }

    public function addRevision(Revision $newRevision): void
    {
        $this->revisions->add($newRevision);

        if ($newRevision->isDraft()) {
            return;
        }

        if ($newRevision->isArchived() === null) {
            foreach ($this->revisions as $revision) {
                if ($revision !== $newRevision && $revision->getLocale() === $newRevision->getLocale()) {
                    $revision->archive();
                }
            }
        }
    }

    public function addChildRevision(Revision $newRevision): void
    {
        $this->childRevisions->add($newRevision);
    }

    public function getActiveRevision(Locale|null $locale = null): Revision
    {
        $locale ??= Locale::default();

        return $this->revisions->filter(
            static fn (Revision $revision) => $revision->getLocale() === $locale && !$revision->isDraft()
        )->first() ?? throw new NotFoundHttpException('Revision not found');
    }

    public function isHome(): bool
    {
        return $this->hasId() && $this->id === self::PAGE_ID_HOME;
    }

    public function is404(): bool
    {
        return $this->hasId() && $this->id === self::PAGE_ID_404;
    }

    public function canBeRemoved(): bool
    {
        return $this->hasId() && $this->id >= self::PAGE_ID_START;
    }
}
