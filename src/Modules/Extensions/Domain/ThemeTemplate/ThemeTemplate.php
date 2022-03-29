<?php

namespace ForkCMS\Modules\Extensions\Domain\ThemeTemplate;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ForkCMS\Core\Domain\Settings\EntityWithSettingsTrait;
use ForkCMS\Modules\Backend\Domain\Action\ModuleAction;
use ForkCMS\Modules\Backend\Domain\User\Blameable;
use ForkCMS\Modules\Extensions\Domain\Theme\Theme;
use Pageon\DoctrineDataGridBundle\Attribute\DataGrid;
use Pageon\DoctrineDataGridBundle\Attribute\DataGridActionColumn;
use Pageon\DoctrineDataGridBundle\Attribute\DataGridMethodColumn;
use Pageon\DoctrineDataGridBundle\Attribute\DataGridPropertyColumn;

#[ORM\Entity(repositoryClass: ThemeTemplateRepository::class)]
#[ORM\Table(name: 'extensions__theme_template')]
#[DataGrid('ThemeTemplate')]
#[DataGridActionColumn(
    route: 'backend_action',
    routeAttributes: [
        'module' => 'extensions',
        'action' => 'theme_template_edit',
    ],
    routeAttributesCallback: [self::class, 'dataGridEditLinkCallback'],
    label: 'lbl.Edit',
    iconClass: 'edit',
    requiredRole: ModuleAction::ROLE_PREFIX . 'EXTENSIONS__THEME_TEMPLATE_EDIT',
)]
class ThemeTemplate
{
    public const PATH_DIRECTORY = 'templates/Core/';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private int $id;

    #[ORM\Column(type: Types::STRING)]
    #[DataGridPropertyColumn(label: 'lbl.Name')]
    private string $name;

    #[ORM\Column(type: Types::STRING)]
    private string $path;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $active;

    #[ORM\ManyToOne(targetEntity: Theme::class, inversedBy: "templates")]
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
        $entity = $dataTransferObject->getEntity() ?? new self($dataTransferObject->theme);
        $entity->name = $dataTransferObject->name;
        $entity->path = self::PATH_DIRECTORY . $dataTransferObject->path;
        $entity->settings = $dataTransferObject->settings;
        $entity->active = $dataTransferObject->active;
        if ($dataTransferObject->default) {
            $entity->theme->getDefaultTemplate()->defaultForTheme = null;
            $entity->defaultForTheme = $entity->theme;
            $entity->theme->changeDefaultTemplate($entity);
        }

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

    #[DataGridMethodColumn(label: 'lbl.Active')]
    public function getActive(): bool
    {
        return $this->active;
    }

    public function getTheme(): Theme
    {
        return $this->theme;
    }

    #[DataGridMethodColumn(label: 'lbl.Default')]
    public function isDefault(): bool
    {
        return $this->defaultForTheme !== null;
    }

    public static function dataGridEditLinkCallback(self $themeTemplate): array
    {
        return ['slug' => $themeTemplate->getId()];
    }

    public function getFullPath(): string
    {
        return $this->theme->getPath() . '/' . $this->path;
    }

    public function getPositions(): array
    {
        return $this->getSetting('positions', []);
    }
}
