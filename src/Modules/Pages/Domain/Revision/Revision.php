<?php

namespace ForkCMS\Modules\Pages\Domain\Revision;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Event\LifecycleEventArgs;
use ForkCMS\Core\Domain\Settings\EntityWithSettingsTrait;
use ForkCMS\Core\Domain\Settings\SettingsBag;
use ForkCMS\Modules\Backend\Domain\User\Blameable;
use ForkCMS\Modules\Extensions\Domain\Module\Module;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleName;
use ForkCMS\Modules\Frontend\Domain\Meta\EntityWithMetaTrait;
use ForkCMS\Modules\Frontend\Domain\Meta\Meta;
use ForkCMS\Modules\Internationalisation\Domain\Locale\EntityWithLocaleTrait;
use ForkCMS\Modules\Internationalisation\Domain\Locale\Locale;
use ForkCMS\Modules\Pages\Domain\Page\Page;
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
    #[ORM\ManyToOne(targetEntity: Page::class, cascade: ['persist'], inversedBy: 'revisions')]
    private Page $page;

    #[ORM\ManyToOne(targetEntity: Page::class, inversedBy: 'childRevisions')]
    private ?Page $parentPage;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private int $id;

    #[ORM\Column(type: Types::STRING)]
    private string $title;

    #[ORM\Column(type: Types::TEXT)]
    private string $content;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $isDraft;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private DateTimeImmutable|null $isArchived = null;

    use EntityWithSettingsTrait;
    use EntityWithMetaTrait;
    use EntityWithLocaleTrait;
    use Blameable;

    public function __construct(
        string $title,
        string $content,
        ?Page $page = null,
        bool $isDraft = false,
        ?Locale $locale = null,
        ?Page $parentPage = null,
    ) {
        $this->locale = $locale ?? Locale::default();
        $this->title = $title;
        $this->content = $content;
        $this->isDraft = $isDraft;
        if ($isDraft) {
            $this->archive();
        }
        $this->page = $page ?? new Page($this->locale);
        $this->page->addRevision($this);
        $this->parentPage = $parentPage;
        $this->parentPage?->addChildRevision($this);
        $this->settings = new SettingsBag();
        $this->meta = Meta::forName($title);
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
        return $this->content;
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
        return 'pages__revision__' . $this->page->getId() . '.' . $this->locale->value;
    }
}
