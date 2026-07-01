<?php

declare(strict_types=1);

namespace ForkCMS\Modules\Frontend\Domain\Meta;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ForkCMS\Core\Domain\Settings\EntityWithSettingsTrait;
use ForkCMS\Core\Domain\Settings\SettingsBag;
use ForkCMS\Modules\Backend\Domain\User\Blameable;
use Symfony\Component\String\Slugger\AsciiSlugger;
use JsonSerializable;

#[ORM\Entity(repositoryClass: MetaRepository::class)]
#[ORM\Index(name: 'idx_slug', columns: ['slug'])]
#[ORM\HasLifecycleCallbacks]
class Meta implements JsonSerializable
{
    use EntityWithSettingsTrait;

    use Blameable;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private(set) int $id;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private(set) string $keywords;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private(set) bool $keywordsOverwrite;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private(set) string $description;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private(set) bool $descriptionOverwrite;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private(set) string $title;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private(set) bool $titleOverwrite;

    #[ORM\Column(type: Types::STRING, length: 255)]
    public string $slug;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private(set) bool $slugOverwrite;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private(set) ?string $canonicalUrl;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private(set) bool $canonicalUrlOverwrite;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private(set) ?string $custom;

    #[ORM\Column(type: Types::STRING, enumType: SEOFollow::class)]
    private(set) SEOFollow $seoFollow;

    #[ORM\Column(type: Types::STRING, enumType: SEOIndex::class)]
    private(set) SEOIndex $seoIndex;

    public function __construct(
        string $keywords,
        bool $keywordsOverwrite,
        string $description,
        bool $descriptionOverwrite,
        string $title,
        bool $titleOverwrite,
        string $slug,
        bool $slugOverwrite,
        ?string $canonicalUrl = null,
        bool $canonicalUrlOverwrite = false,
        ?string $custom = null,
        ?SEOFollow $seoFollow = null,
        ?SEOIndex $seoIndex = null,
        ?SettingsBag $settings = null,
    ) {
        $this->settings = $settings ?? new SettingsBag();
        $this->update(...func_get_args());
    }

    public function update(
        string $keywords,
        bool $keywordsOverwrite,
        string $description,
        bool $descriptionOverwrite,
        string $title,
        bool $titleOverwrite,
        string $slug,
        bool $slugOverwrite,
        ?string $canonicalUrl = null,
        bool $canonicalUrlOverwrite = false,
        ?string $custom = null,
        ?SEOFollow $seoFollow = null,
        ?SEOIndex $seoIndex = null,
        ?SettingsBag $settings = null,
    ): void {
        $this->keywords = $keywords;
        $this->keywordsOverwrite = $keywordsOverwrite;
        $this->description = $description;
        $this->descriptionOverwrite = $descriptionOverwrite;
        $this->title = $title;
        $this->titleOverwrite = $titleOverwrite;
        $this->slug = $slug;
        $this->slugOverwrite = $slugOverwrite;
        $this->custom = $custom;
        $this->seoFollow = $seoFollow ?? SEOFollow::NONE;
        $this->seoIndex = $seoIndex ?? SEOIndex::NONE;
        $this->canonicalUrl = $canonicalUrl;
        $this->canonicalUrlOverwrite = $canonicalUrlOverwrite;
        $this->settings = $settings ?? $this->settings;
    }

    public static function forName(string $title): self
    {
        return new self(
            $title,
            false,
            $title,
            false,
            $title,
            false,
            strtolower(new AsciiSlugger()->slug($title)->toString()),
            false
        );
    }

    /** @return array<string, mixed> */
    #[\Override]
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'keywords' => $this->keywords,
            'keywordsOverwrite' => $this->keywordsOverwrite,
            'description' => $this->description,
            'descriptionOverwrite' => $this->descriptionOverwrite,
            'title' => $this->title,
            'titleOverwrite' => $this->titleOverwrite,
            'settings' => $this->settings,
            'slug' => $this->slug,
            'slugOverwrite' => $this->slugOverwrite,
            'custom' => $this->custom,
            'seoFollow' => $this->seoFollow,
            'seoIndex' => $this->seoIndex,
        ];
    }
}
