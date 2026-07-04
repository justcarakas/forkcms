<?php

declare(strict_types=1);

namespace ForkCMS\Modules\ContentBlocks\Domain\ContentBlock;

use DateTimeImmutable;
use ForkCMS\Core\Domain\Form\Validator\UniqueDataTransferObject;
use ForkCMS\Core\Domain\Form\Validator\UniqueDataTransferObjectInterface;
use ForkCMS\Core\Domain\Settings\SettingsBag;
use Symfony\Component\Validator\Constraints as Assert;

/** @implements UniqueDataTransferObjectInterface<Revision> */
#[UniqueDataTransferObject(
    fields: 'title',
    entityClass: Revision::class,
    repositoryMethod: 'findActiveForCurrentLocaleByTitle',
    errorPath: 'title',
)]
abstract class ContentBlockDataTransferObject implements UniqueDataTransferObjectInterface
{
    protected ?Revision $revisionEntity;

    #[Assert\NotBlank(message: 'err.FieldIsRequired')]
    public ?string $title = null;

    #[Assert\NotBlank(message: 'err.FieldIsRequired')]
    public string $template = Revision::DEFAULT_TEMPLATE;

    #[Assert\NotBlank(message: 'err.FieldIsRequired')]
    public ?string $text = null;

    public bool $isEnabled = true;

    public ?DateTimeImmutable $archivedOn = null;

    public SettingsBag $settings;

    public function __construct(?Revision $revisionEntity = null)
    {
        $this->revisionEntity = $revisionEntity;

        if (!$revisionEntity instanceof Revision) {
            $this->settings = new SettingsBag();

            return;
        }

        $this->isEnabled = $revisionEntity->contentBlock->isWidgetVisible();
        $this->title = $revisionEntity->title;
        $this->text = $revisionEntity->text;
        $this->template = $revisionEntity->template;
        $this->settings = $revisionEntity->settings;
    }

    public function hasEntity(): bool
    {
        return $this->revisionEntity instanceof Revision;
    }

    public function getEntity(): ?Revision
    {
        return $this->revisionEntity;
    }
}
