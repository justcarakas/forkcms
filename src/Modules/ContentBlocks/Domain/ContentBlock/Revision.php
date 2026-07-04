<?php

declare(strict_types=1);

namespace ForkCMS\Modules\ContentBlocks\Domain\ContentBlock;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ForkCMS\Core\Domain\Settings\EntityWithSettingsTrait;
use ForkCMS\Core\Domain\Settings\SettingsBag;
use ForkCMS\Modules\Backend\Domain\Action\ModuleAction;
use ForkCMS\Modules\Backend\Domain\User\Blameable\CreatedBy;
use ForkCMS\Modules\ContentBlocks\Backend\Actions\ContentBlockEdit;
use Pageon\DoctrineDataGridBundle\Attribute\DataGrid;
use Pageon\DoctrineDataGridBundle\Attribute\DataGridPropertyColumn;

#[ORM\Entity(repositoryClass: RevisionRepository::class)]
#[DataGrid('Revision', noResultsMessage: 'msg.NoRevisions')]
#[ORM\HasLifecycleCallbacks]
class Revision
{
    use CreatedBy;
    use EntityWithSettingsTrait;

    public const string DEFAULT_TEMPLATE = 'Default.html.twig';
    public const string SETTING_MAX_REVISIONS_NAME = 'max_revisions';
    public const int SETTING_MAX_REVISIONS_DEFAULT = 10;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private(set) int $id;

    #[ORM\ManyToOne(targetEntity: ContentBlock::class, cascade: ['persist'], inversedBy: 'revisions')]
    #[ORM\JoinColumn(nullable: false)]
    private(set) ContentBlock $contentBlock;

    #[ORM\Column(type: Types::STRING, options: ['default' => self::DEFAULT_TEMPLATE])]
    private(set) string $template;

    #[ORM\Column(type: Types::STRING)]
    #[DataGridPropertyColumn(
        sortable: true,
        filterable: true,
        label: 'lbl.Title',
        route: 'backend_action',
        routeAttributesCallback: [self::class, 'dataGridEditLinkCallback'],
        routeRole: ModuleAction::ROLE_PREFIX . 'CONTENT_BLOCKS__CONTENT_BLOCK_EDIT',
        columnAttributes: ['class' => 'title'],
    )]
    private(set) string $title;

    #[ORM\Column(type: Types::TEXT)]
    private(set) string $text;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private(set) ?DateTimeImmutable $archivedOn = null;

    private function __construct(
        ContentBlock $contentBlock,
        string $template,
        string $title,
        string $text,
        SettingsBag $settings
    ) {
        $this->contentBlock = $contentBlock;
        $this->template = $template;
        $this->title = $title;
        $this->text = $text;
        $this->settings = $settings;
    }

    public static function fromDataTransferObject(
        ContentBlockDataTransferObject $dataTransferObject,
        ContentBlock $contentBlock
    ): self {
        $entity = new self(
            $contentBlock,
            $dataTransferObject->template,
            $dataTransferObject->title,
            $dataTransferObject->text,
            $dataTransferObject->settings,
        );

        $widget = $contentBlock->widget;
        $dataTransferObject->isEnabled ? $widget->enable() : $widget->disable();
        $widget->settings->set('label', $dataTransferObject->title);

        return $entity;
    }

    /**
     * @param array{string?: string} $attributes
     *
     * @return array{string?: int|string}
     */
    public static function dataGridEditLinkCallback(self $revision, array $attributes): array
    {
        $attributes = array_merge(ContentBlockEdit::getActionSlug()->getRouteParameters(), $attributes);
        $attributes['slug'] = $revision->contentBlock->id;
        $attributes['revision'] = $revision->id;

        return $attributes;
    }

    public function archive(): void
    {
        $this->archivedOn = new DateTimeImmutable();
    }
}
