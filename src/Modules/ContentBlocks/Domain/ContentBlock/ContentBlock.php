<?php

namespace ForkCMS\Modules\ContentBlocks\Domain\ContentBlock;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Mapping as ORM;
use ForkCMS\Core\Domain\Settings\EntityWithSettingsTrait;
use ForkCMS\Modules\Backend\Domain\Action\ModuleAction;
use ForkCMS\Modules\Backend\Domain\User\Blameable;
use ForkCMS\Modules\Internationalisation\Domain\Locale\EntityWithLocaleTrait;
use ForkCMS\Modules\Frontend\Domain\Block\Block;
use ForkCMS\Modules\Internationalisation\Domain\Translation\TranslationKey;
use Pageon\DoctrineDataGridBundle\Attribute\DataGrid;
use Pageon\DoctrineDataGridBundle\Attribute\DataGridActionColumn;
use Pageon\DoctrineDataGridBundle\Attribute\DataGridMethodColumn;
use Pageon\DoctrineDataGridBundle\Attribute\DataGridPropertyColumn;

#[ORM\Entity(repositoryClass: ContentBlockRepository::class)]
#[DataGrid('ContentBlock', noResultsMessage: 'msg.NoContentBlocksFound')]
#[ORM\HasLifecycleCallbacks]
#[DataGridActionColumn(
    route: 'backend_action',
    routeAttributes: [
        'module' => 'content-blocks',
        'action' => 'content-block-edit',
    ],
    routeAttributesCallback: [self::class, 'dataGridEditLinkCallback'],
    label: 'lbl.Edit',
    class: 'btn btn-primary btn-sm',
    iconClass: 'fa fa-edit',
    requiredRole: ModuleAction::ROLE_PREFIX . 'CONTENT_BLOCKS__CONTENT_BLOCK_EDIT',
    columnAttributes: ['class' => 'fork-data-grid-action'],
)]
class ContentBlock
{
    use Blameable;
    use EntityWithLocaleTrait;
    use EntityWithSettingsTrait;

    public const string DEFAULT_TEMPLATE = 'Default.html.twig';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'revision_id', type: Types::INTEGER)]
    /** @phpstan-ignore-next-line */
    private(set) int $revisionId;

    #[ORM\Column(type: Types::INTEGER)]
    private(set) int $id;

    #[ORM\ManyToOne(targetEntity: Block::class, cascade: ['persist'], fetch: 'EAGER')]
    #[ORM\JoinColumn(nullable: false)]
    private(set) Block $widget;

    #[ORM\Column(type: Types::STRING, options: ['default' => self::DEFAULT_TEMPLATE])]
    private(set) string $template;

    #[ORM\Column(type: Types::STRING)]
    #[DataGridPropertyColumn(
        sortable: true,
        filterable: true,
        label: 'lbl.Title',
        route: 'backend_action',
        routeAttributes: [
            'module' => 'content-blocks',
            'action' => 'content-block-edit',
        ],
        routeAttributesCallback: [self::class, 'dataGridEditLinkCallback'],
        routeRole: ModuleAction::ROLE_PREFIX . 'CONTENT_BLOCKS__CONTENT_BLOCK_EDIT',
        columnAttributes: ['class' => 'title'],
    )]
    private(set) string $title;

    #[ORM\Column(type: Types::TEXT)]
    private(set) string $text;

    #[ORM\Column(name: 'hidden', type: Types::BOOLEAN, options: ['default' => false])]
    private(set) bool $isHidden;

    #[ORM\Column(type: Types::STRING, enumType: Status::class, options: ['default' => Status::ACTIVE->value])]
    private(set) Status $status;

    private function __construct(ContentBlockDataTransferObject $dataTransferObject)
    {
        $this->id = $dataTransferObject->id;
        $this->widget = $dataTransferObject->widget;
        $this->template = $dataTransferObject->template;
        $this->locale = $dataTransferObject->locale;
        $this->title = $dataTransferObject->title;
        $this->text = $dataTransferObject->text;
        $this->isHidden = !$dataTransferObject->isVisible;
        $this->status = $dataTransferObject->status;
        $this->settings = $dataTransferObject->settings;
    }

    #[DataGridMethodColumn(label: 'lbl.VisibleOnSite')]
    public function isVisible(): bool
    {
        return !$this->isHidden;
    }

    public function archive(): void
    {
        $this->status = Status::ARCHIVED;
    }

    public function activate(): void
    {
        $this->status = Status::ACTIVE;
    }

    public static function fromDataTransferObject(ContentBlockDataTransferObject $dataTransferObject): self
    {
        return new self($dataTransferObject);
    }

    /**
     * @param array{string?: string} $attributes
     *
     * @return array{string?: int|string}
     */
    public static function dataGridEditLinkCallback(self $contentBlock, array $attributes): array
    {
        $attributes['slug'] = $contentBlock->revisionId;

        return $attributes;
    }

    #[ORM\PostPersist]
    #[ORM\PostUpdate]
    public function prePersist(PostPersistEventArgs|PostUpdateEventArgs $args): void
    {
        if ($this->status === Status::ARCHIVED) {
            return;
        }
        $objectManager = $args->getObjectManager();
        $this->widget->settings->add([
            'label' => $this->title,
            'content_block_id' => $this->id,
        ]);
        if ($this->isHidden) {
            $this->widget->hide();
        } else {
            $this->widget->show();
        }

        $objectManager->flush();
    }
}
