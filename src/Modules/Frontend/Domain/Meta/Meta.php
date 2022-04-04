<?php

namespace ForkCMS\Modules\Frontend\Domain\Meta;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Mapping as ORM;
use ForkCMS\Core\Domain\Settings\EntityWithSettingsTrait;
use ForkCMS\Core\Domain\Settings\SettingsBag;
use ForkCMS\Modules\Backend\Domain\User\Blameable;
use ForkCMS\Modules\Frontend\Domain\Meta\Subject\MetaSubjectInterface;
use ForkCMS\Modules\Frontend\Domain\Meta\Subject\MetaSubjectReference;
use JsonSerializable;

#[ORM\Entity(repositoryClass: MetaRepository::class)]
#[ORM\Table(name: 'frontend__meta')]
#[ORM\Index(columns: ['url'], name: 'idx_url')]
#[ORM\HasLifecycleCallbacks]
class Meta implements JsonSerializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private int $id;

    #[ORM\Column(type: Types::STRING)]
    private string $keywords;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $keywordsOverwrite;

    #[ORM\Column(type: Types::STRING)]
    private string $description;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $descriptionOverwrite;

    #[ORM\Column(type: Types::STRING)]
    private string $title;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $titleOverwrite;

    #[ORM\Column(type: Types::STRING)]
    private string $url;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $urlOverwrite;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private string|null $canonicalUrl;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $canonicalUrlOverwrite;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private string|null $custom;

    use EntityWithSettingsTrait;

    #[ORM\Column(type: SEOFollowDBALType::NAME, nullable: true)]
    private SEOFollow|null $seoFollow;

    #[ORM\Column(type: SEOIndexDBALType::NAME, nullable: true)]
    private SEOIndex|null $seoIndex;

    private MetaSubjectInterface $subject;

    #[ORM\Embedded(class: MetaSubjectReference::class)]
    private MetaSubjectInterface $subjectReference;

    use Blameable;

    public function __construct(
        string $keywords,
        bool $keywordsOverwrite,
        string $description,
        bool $descriptionOverwrite,
        string $title,
        bool $titleOverwrite,
        string $url,
        bool $urlOverwrite,
        ?string $canonicalUrl = null,
        bool $canonicalUrlOverwrite = false,
        string $custom = null,
        SEOFollow $seoFollow = null,
        SEOIndex $seoIndex = null,
        SettingsBag $settings = null,
        int $id = null
    ) {
        $this->settings = $settings ?? new SettingsBag();
        $this->id = $id;
        $this->update(...func_get_args());
    }

    public function update(
        string $keywords,
        bool $keywordsOverwrite,
        string $description,
        bool $descriptionOverwrite,
        string $title,
        bool $titleOverwrite,
        string $url,
        bool $urlOverwrite,
        ?string $canonicalUrl = null,
        bool $canonicalUrlOverwrite = false,
        string $custom = null,
        SEOFollow $seoFollow = null,
        SEOIndex $seoIndex = null,
        SettingsBag $settings = null,
    ): void {
        $this->keywords = $keywords;
        $this->keywordsOverwrite = $keywordsOverwrite;
        $this->description = $description;
        $this->descriptionOverwrite = $descriptionOverwrite;
        $this->title = $title;
        $this->titleOverwrite = $titleOverwrite;
        $this->url = $url;
        $this->urlOverwrite = $urlOverwrite;
        $this->custom = $custom;
        $this->seoFollow = $seoFollow;
        $this->seoIndex = $seoIndex;
        $this->canonicalUrl = $canonicalUrl;
        $this->canonicalUrlOverwrite = $canonicalUrlOverwrite;
        $this->settings = $settings ?? $this->settings;
    }

    /**
     * Used in the transformer of the Symfony form type for this entity
     *
     * @param array $metaData
     *
     * @return self
     */
    public static function updateWithFormData(array $metaData): self
    {
        return new self(
            $metaData['keywords'],
            $metaData['keywordsOverwrite'],
            $metaData['description'],
            $metaData['descriptionOverwrite'],
            $metaData['title'],
            $metaData['titleOverwrite'],
            $metaData['url'],
            $metaData['urlOverwrite'],
            $metaData['canonical_url'],
            $metaData['canonical_url_overwrite'],
            $metaData['custom'] ?? null,
            SEOFollow::fromString((string) $metaData['SEOFollow']),
            SEOIndex::fromString((string) $metaData['SEOIndex']),
            null,
            (int) $metaData['id']
        );
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getKeywords(): string
    {
        return $this->keywords;
    }

    public function isKeywordsOverwrite(): bool
    {
        return $this->keywordsOverwrite;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function isDescriptionOverwrite(): bool
    {
        return $this->descriptionOverwrite;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function isTitleOverwrite(): bool
    {
        return $this->titleOverwrite;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function isUrlOverwrite(): bool
    {
        return $this->urlOverwrite;
    }

    public function getCanonicalUrl(): ?string
    {
        return $this->canonicalUrl;
    }

    public function isCanonicalUrlOverwrite(): bool
    {
        return $this->canonicalUrlOverwrite;
    }

    public function getCustom(): ?string
    {
        return $this->custom;
    }

    public function hasSEOIndex(): bool
    {
        return !$this->seoIndex->isNone();
    }

    public function getSEOIndex(): ?SEOIndex
    {
        if (!$this->hasSEOIndex()) {
            return null;
        }

        return $this->seoIndex;
    }

    public function hasSEOFollow(): bool
    {
        return $this->seoFollow instanceof SEOFollow && !$this->seoFollow->isNone();
    }

    public function getSEOFollow(): ?SEOFollow
    {
        if (!$this->hasSEOFollow()) {
            return null;
        }

        return $this->seoFollow;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'keywords' => $this->getKeywords(),
            'keywordsOverwrite' => $this->isKeywordsOverwrite(),
            'description' => $this->getDescription(),
            'descriptionOverwrite' => $this->isDescriptionOverwrite(),
            'title' => $this->getTitle(),
            'titleOverwrite' => $this->isTitleOverwrite(),
            'settings' => $this->getSettings(),
            'url' => $this->getUrl(),
            'urlOverwrite' => $this->isUrlOverwrite(),
            'custom' => $this->getCustom(),
            'seoFollow' => $this->getSEOFollow(),
            'seoIndex' => $this->getSEOIndex(),
        ];
    }

    public function setSubject(
        MetaSubjectInterface $subject,
        bool $overwriteSubject = false,
        bool $overwriteReference = true
    ): void {
        if ($overwriteSubject || !isset($this->subject)) {
            $this->subject = $subject;
        }
        if ($overwriteReference || !isset($this->subjectReference)) {
            $this->subjectReference = MetaSubjectReference::fromMetaSubject($this->subject);
        }
    }

    public function getSubject(): MetaSubjectInterface
    {
        return $this->subject;
    }

    #[ORM\PreFlush]
    public function doctrinePreFlush(PreFlushEventArgs $args): void
    {
        $this->setSubject($this->subject); // make sure the reference is up to date
    }

    #[ORM\PostLoad]
    public function doctrinePostLoad(LifecycleEventArgs $args): void
    {
        $this->setSubject(
            $args->getEntityManager()->getPartialReference(
                $this->subject->getClassName(),
                $this->subject->getId()
            ),
            false,
            false
        );
    }
}
