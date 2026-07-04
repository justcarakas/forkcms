<?php

declare(strict_types=1);

namespace ForkCMS\Modules\Extensions\Domain\ThemeTemplate;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use ForkCMS\Core\Domain\Settings\EntityWithSettingsTrait;
use ForkCMS\Modules\Backend\Domain\Action\ModuleAction;
use ForkCMS\Modules\Backend\Domain\User\Blameable\CreatedAndUpdatedBy;
use ForkCMS\Modules\Extensions\Domain\Theme\Theme;
use ForkCMS\Modules\Internationalisation\Domain\Locale\Locale;
use JsonSerializable;
use Pageon\DoctrineDataGridBundle\Attribute\DataGrid;
use Pageon\DoctrineDataGridBundle\Attribute\DataGridActionColumn;
use Pageon\DoctrineDataGridBundle\Attribute\DataGridMethodColumn;
use Pageon\DoctrineDataGridBundle\Attribute\DataGridPropertyColumn;
use Stringable;

#[ORM\Entity(repositoryClass: ThemeTemplateRepository::class)]
#[DataGrid('ThemeTemplate')]
#[DataGridActionColumn(
    route: 'backend_action',
    routeAttributes: [
        'module' => 'extensions',
        'action' => 'theme-template-edit',
    ],
    routeAttributesCallback: [self::class, 'dataGridEditLinkCallback'],
    label: 'lbl.Edit',
    iconClass: 'edit',
    requiredRole: ModuleAction::ROLE_PREFIX . 'EXTENSIONS__THEME_TEMPLATE_EDIT',
)]
class ThemeTemplate implements JsonSerializable, Stringable
{
    use EntityWithSettingsTrait;

    use CreatedAndUpdatedBy;

    public const string PATH_DIRECTORY = 'Frontend/base/';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private(set) int $id;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[DataGridPropertyColumn(label: 'lbl.Name')]
    private(set) string $name;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private(set) string $path;

    #[ORM\Column(type: Types::BOOLEAN)]
    private(set) bool $active;

    #[ORM\ManyToOne(targetEntity: Theme::class, inversedBy: 'templates')]
    #[ORM\JoinColumn(name: 'theme', referencedColumnName: 'name', nullable: false)]
    private(set) Theme $theme;

    #[ORM\OneToOne(targetEntity: Theme::class, mappedBy: 'defaultTemplate')]
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

    #[DataGridMethodColumn(label: 'lbl.Default')]
    public function isDefault(): bool
    {
        return $this->defaultForTheme !== null;
    }

    /**
     * @param array{string?: string} $attributes
     *
     * @return array{string?: int|string}
     */
    public static function dataGridEditLinkCallback(self $themeTemplate, array $attributes): array
    {
        $attributes['slug'] = $themeTemplate->id;

        return $attributes;
    }

    public function getFullPath(): string
    {
        return $this->theme->getPath() . '/' . $this->path;
    }

    /** @return array<int, array<string, string|array<int, int>>> */
    public function getPositions(): array
    {
        return $this->getSetting('positions', []);
    }

    public function getTemplatePath(): string
    {
        return '@Frontend/base/' . str_replace(self::PATH_DIRECTORY, '', $this->path);
    }

    /** @return array<string, mixed> */
    #[\Override]
    public function jsonSerialize(): array
    {
        $json = [
            'id' => $this->id,
            'name' => $this->name,
            'path' => $this->path,
            'theme' => $this->theme,
            'is_default' => $this->isDefault(),
            'settings' => $this->settings->all(),
            'has_block' => false,
        ];

        $currentLocale = Locale::current();
        $json['settings']['default_extras'] = $json['settings']['default_extras'][$currentLocale->value] ?? [];

        // validate
        if (!isset($json['settings']['layout'])) {
            throw new Exception('Invalid template-format.');
        }

        return $json;
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->name;
    }
}
