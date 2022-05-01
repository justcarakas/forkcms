<?php

namespace ForkCMS\Modules\Pages\Domain\Revision;

use DateTimeImmutable;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Event\LifecycleEventArgs;
use ForkCMS\Core\Domain\Settings\EntityWithSettingsTrait;
use ForkCMS\Core\Domain\Settings\SettingsBag;
use ForkCMS\Modules\Backend\Domain\User\Blameable;
use ForkCMS\Modules\Extensions\Domain\Module\Module;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleName;
use ForkCMS\Modules\Extensions\Domain\ThemeTemplate\ThemeTemplate;
use ForkCMS\Modules\Frontend\Domain\Meta\EntityWithMetaTrait;
use ForkCMS\Modules\Frontend\Domain\Meta\Meta;
use ForkCMS\Modules\Internationalisation\Domain\Locale\EntityWithLocaleTrait;
use ForkCMS\Modules\Internationalisation\Domain\Locale\Locale;
use ForkCMS\Modules\Pages\Domain\Page\Page;
use ForkCMS\Modules\Pages\Domain\RevisionBlock\RevisionBlock;
use ForkCMS\Modules\Pages\Domain\RevisionBlock\RevisionBlockDataTransferObject;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

/**
 * @Gedmo\SoftDeleteable(fieldName="isArchived", timeAware=true)
 */
#[ORM\Entity(repositoryClass: RevisionRepository::class)]
#[ORM\Table(name: 'pages__revision')]
#[ORM\HasLifecycleCallbacks]
class Revision
{
    use EntityWithSettingsTrait;
    use EntityWithMetaTrait;
    use EntityWithLocaleTrait;
    use Blameable;

    #[ORM\ManyToOne(targetEntity: Page::class, cascade: ['persist'], inversedBy: 'revisions')]
    private Page $page;

    #[ORM\ManyToOne(targetEntity: Page::class, inversedBy: 'childRevisions')]
    private ?Page $parentPage;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private int $id;

    #[ORM\Column(type: Types::STRING, enumType: MenuType::class)]
    private MenuType $type;

    #[ORM\Column(type: Types::STRING)]
    private string $title;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $isDraft;

    #[ORM\ManyToOne(targetEntity: ThemeTemplate::class)]
    private ThemeTemplate $themeTemplate;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private DateTimeImmutable|null $isArchived = null;

    #[ORM\OneToMany(mappedBy: 'revision', targetEntity: RevisionBlock::class)]
    private Collection $blocks;

    /** @param Collection<RevisionBlockDataTransferObject> $blocks  */
    private function __construct(
        Page $page,
        ?Page $parentPage,
        MenuType $type,
        string $title,
        bool $isDraft,
        ThemeTemplate $themeTemplate,
        ?DateTimeImmutable $isArchived,
        Collection $blocks,
        Meta $meta,
        Locale $locale,
        SettingsBag $settings,
    ) {
        $this->page = $page;
        $this->parentPage = $parentPage;
        $this->type = $type;
        $this->title = $title;
        $this->isDraft = $isDraft;
        $this->themeTemplate = $themeTemplate;
        $this->isArchived = $isArchived;
        $this->blocks = $blocks->map(function (RevisionBlockDataTransferObject $block): RevisionBlock {
            $block->revision = $this;

            return RevisionBlock::fromDataTransferObject($block);
        });
        $this->meta = $meta;
        $this->locale = $locale;
        $this->settings = $settings;

        if ($isDraft) {
            $this->archive();
        }
        $this->page->addRevision($this);
        $this->parentPage?->addChildRevision($this);
    }

    public static function fromDataTransferObject(RevisionDataTransferObject $revisionDataTransferObject): self
    {
        return new self(
            $revisionDataTransferObject->page,
            $revisionDataTransferObject->parentPage,
            $revisionDataTransferObject->type,
            $revisionDataTransferObject->title,
            $revisionDataTransferObject->isDraft,
            $revisionDataTransferObject->themeTemplate,
            $revisionDataTransferObject->isArchived,
            $revisionDataTransferObject->blocks,
            $revisionDataTransferObject->meta,
            $revisionDataTransferObject->locale,
            new SettingsBag($revisionDataTransferObject->settings),
        );
    }

    public function getPage(): Page
    {
        return $this->page;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getContent(): string
    {
        return 'test' . $this->locale->value;
    }

    public function isArchived(): ?DateTimeImmutable
    {
        return $this->isArchived;
    }

    public function isDraft(): bool
    {
        return $this->isDraft;
    }

    public function archive(): void
    {
        if ($this->isArchived === null) {
            $this->isArchived = new DateTimeImmutable();
        }
    }

    public function getParentPage(): ?Page
    {
        return $this->parentPage;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getLocale(): Locale
    {
        return $this->locale;
    }

    public function getMeta(): Meta
    {
        return $this->meta;
    }

    public function getSettings(): SettingsBag
    {
        return $this->settings;
    }

    public function getType(): MenuType
    {
        return $this->type;
    }

    public function getThemeTemplate(): ThemeTemplate
    {
        return $this->themeTemplate;
    }

    public function getArchivedDate(): ?DateTimeImmutable
    {
        return $this->isArchived;
    }

    public function getBlocks(): Collection
    {
        return $this->blocks;
    }

    #[ORM\PrePersist]
    public function cleanupOldRevisions(LifecycleEventArgs $args):void
    {
        $entityManager = $args->getEntityManager();
        $entityManager->getFilters()->disable('softdeleteable');
        /** @var self[] $revisions */
        $revisions = $entityManager->getRepository(self::class)->findBy(['page' => $this->page], ['id' => 'DESC']);
        $counter = 0;
        $moduleRepository = $entityManager->getRepository(Module::class);
        $maxRevisions = $moduleRepository
            ->findOneBy(['name' => ModuleName::fromFQCN($moduleRepository::class)])
            ->getSettings()
            ->getOr('max_revisions', 2);
        foreach ($revisions as $revision) {
            if ($revision->isArchived() === null || $revision->getLocale() !== $this->getLocale()) {
                continue;
            }
            if ($revision->isDraft()) {
                $entityManager->remove($revision);
                continue;
            }
            ++$counter;
            if ($counter > $maxRevisions) {
                $entityManager->remove($revision);
            }
        }
        $entityManager->getFilters()->enable('softdeleteable');
    }

    public function __toString()
    {
        return $this->title;
    }

    public function getRouteName(): string
    {
        return 'pages__page__' . $this->page->getId() . '.' . $this->locale->value;
    }
}
