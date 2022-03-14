<?php

namespace ForkCMS\Modules\Extensions\Domain\ThemeTemplate;

use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ForkCMS\Core\Domain\Settings\EntityWithSettingsTrait;
use ForkCMS\Core\Domain\Settings\SettingsBag;
use ForkCMS\Modules\Backend\Domain\User\Blameable;
use ForkCMS\Modules\Extensions\Domain\Theme\Theme;

#[ORM\Entity(repositoryClass: ThemeTemplateRepository::class)]
#[ORM\Table(name: 'extensions__theme_template')]
class ThemeTemplate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private int $id;

    #[ORM\Column(type: Types::STRING)]
    private string $name;

    #[ORM\Column(type: Types::STRING)]
    private string $path;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $active;

    #[ORM\ManyToOne(targetEntity: Theme::class, inversedBy:"templates")]
    #[ORM\JoinColumn(name: 'theme', referencedColumnName: 'name')]
    private Theme $theme;

    use EntityWithSettingsTrait;

    use Blameable;

    #[ORM\OneToOne(mappedBy: 'defaultTemplate', targetEntity: Theme::class)]
    private ?Theme $defaultForTheme = null;

    private function __construct(Theme $theme)
    {
        $this->theme = $theme;
    }

    public static function fromDataTransferObject(ThemeTemplateDataTransferObject $dataTransferObject): self
    {
        $entity = $dataTransferObject->isNew() ? new self($dataTransferObject->theme) : $dataTransferObject->getEntity();
        $entity->name = $dataTransferObject->name;
        $entity->path = $dataTransferObject->path;
        $entity->settings = $dataTransferObject->settings;
        $entity->active = $dataTransferObject->active;

        return $entity;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getActive(): bool
    {
        return $this->active;
    }

    public function getTheme(): Theme
    {
        return $this->theme;
    }

    public function isDefault(): bool
    {
        return $this->defaultForTheme !== null;
    }
}
