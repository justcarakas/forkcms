<?php

declare(strict_types=1);

namespace ForkCMS\Modules\Extensions\Domain\Theme;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ForkCMS\Core\Domain\Doctrine\CollectionHelper;
use ForkCMS\Core\Domain\Settings\EntityWithSettingsTrait;
use ForkCMS\Modules\Backend\Domain\User\Blameable\CreatedAndUpdatedBy;
use ForkCMS\Modules\Extensions\Domain\ThemeTemplate\ThemeTemplate;
use ForkCMS\Modules\Extensions\Domain\ThemeTemplate\ThemeTemplateDataTransferObject;
use RuntimeException;

#[ORM\Entity(repositoryClass: ThemeRepository::class)]
class Theme
{
    use EntityWithSettingsTrait;

    use CreatedAndUpdatedBy;

    #[ORM\Id]
    #[ORM\Column(type: Types::STRING, length: 255)]
    private(set) string $name;

    #[ORM\Column(type: Types::TEXT)]
    private(set) string $description;

    #[ORM\Column(type: Types::BOOLEAN)]
    private(set) bool $active;

    /** @var Collection<int|string,ThemeTemplate> */
    #[ORM\OneToMany(targetEntity: ThemeTemplate::class, mappedBy: 'theme', cascade: ['persist', 'remove'])]
    private(set) Collection $templates;

    #[ORM\OneToOne(targetEntity: ThemeTemplate::class, inversedBy: 'defaultForTheme')]
    private(set) ?ThemeTemplate $defaultTemplate;

    private function __construct()
    {
        $this->templates = new ArrayCollection();
    }

    public static function fromDataTransferObject(ThemeDataTransferObject $dataTransferObject): self
    {
        $theme = $dataTransferObject->isNew() ? new self() : $dataTransferObject->getEntity();
        $theme->name = $dataTransferObject->name;
        $theme->description = $dataTransferObject->description;
        $theme->active = $dataTransferObject->active;
        /** @var Collection<int|string,ThemeTemplate|void> $templates */
        $templates = $dataTransferObject->templates->map(
            static function (ThemeTemplateDataTransferObject $template) use ($theme): ThemeTemplate {
                $template->theme = $theme;

                return ThemeTemplate::fromDataTransferObject($template);
            }
        );
        if ($dataTransferObject->defaultTemplate === null) {
            $dataTransferObject->defaultTemplate = $templates->first();
        }
        $theme->defaultTemplate = $dataTransferObject->defaultTemplate;

        CollectionHelper::updateCollection(
            $templates,
            $theme->templates, // @phpstan-ignore-line
            static function (ThemeTemplate $themeTemplate) use ($theme): void {
                if ($theme->templates->contains($themeTemplate)) {
                    return;
                }

                $theme->templates->add($themeTemplate);
            },
            static function (ThemeTemplate $themeTemplate) use ($theme): void {
                if (!$theme->templates->contains($themeTemplate)) {
                    return;
                }

                $theme->templates->removeElement($themeTemplate);
            }
        );
        $theme->settings = $dataTransferObject->settings;

        return $theme;
    }

    public function getDefaultTemplate(): ThemeTemplate
    {
        return $this->defaultTemplate ?? throw new RuntimeException('No default template set');
    }

    public function activate(): void
    {
        $this->active = true;
    }

    public function deactivate(): void
    {
        $this->active = false;
    }

    public function getPath(): string
    {
        return realpath(__DIR__ . '/../../../../Themes/' . $this->name);
    }

    public function getAssetsPath(): string
    {
        return $this->getPath() . '/assets';
    }

    public function changeDefaultTemplate(ThemeTemplate $themeTemplate): void
    {
        $this->defaultTemplate = $themeTemplate;
    }
}
