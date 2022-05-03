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

    public function getPageTreeType(): string
    {
        // calculate tree-type
        $treeType = 'page';
        if ($this->getSetting('hidden', false)) {
            $treeType = 'hidden';
        }

        // homepage should have a special icon
        if ($this->getId() === self::PAGE_ID_HOME) {
            $treeType = 'home';
        } elseif ($this->getId() === self::PAGE_ID_404) {
            $treeType = 'error';
        }// elseif ($this->getId() < self::PAGE_ID_404 && mb_substr_count($page['extra_ids'], $this->getSitemapId()) > 0) {
//            $extraIDs = explode(',', $page['extra_ids']);
//
//            // loop extras
//            foreach ($extraIDs as $id) {
//                // check if this is the sitemap id
//                if ($id == $this->getSitemapId()) {
//                    // set type
//                    $treeType = 'sitemap';
//
//                    // break it
//                    break;
//                }
//            }
//        }
//
//        // any data?
//        if (isset($page['data'])) {
//            // get data
//            $data = unserialize($page['data'], ['allowed_classes' => false]);
//
//            // internal alias?
//            if (isset($data['internal_redirect']['page_id']) && $data['internal_redirect']['page_id'] != '') {
//                $pageData['redirect_page_id'] = $data['internal_redirect']['page_id'];
//                $pageData['redirect_code'] = $data['internal_redirect']['code'];
//                $treeType = 'redirect';
//            }
//
//            // external alias?
//            if (isset($data['external_redirect']['url']) && $data['external_redirect']['url'] != '') {
//                $pageData['redirect_url'] = $data['external_redirect']['url'];
//                $pageData['redirect_code'] = $data['external_redirect']['code'];
//                $treeType = 'redirect';
//            }
//
//            // direct action?
//            if (isset($data['is_action']) && $data['is_action']) {
//                $treeType = 'direct_action';
//            }
//        }

        return $treeType;
    }
}
