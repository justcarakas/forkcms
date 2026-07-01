<?php

declare(strict_types=1);

namespace ForkCMS\Modules\Internationalisation\Domain\Translation;

use ForkCMS\Core\Domain\Form\Validator\UniqueDataTransferObject;
use ForkCMS\Core\Domain\Form\Validator\UniqueDataTransferObjectInterface;
use ForkCMS\Modules\Internationalisation\Domain\Locale\Locale;
use Symfony\Component\Validator\Constraints as Assert;

/** @implements UniqueDataTransferObjectInterface<Translation> */
#[UniqueDataTransferObject(
    fields: ['domain', 'key', 'locale'],
    entityClass: Translation::class,
    repositoryMethod: 'uniqueDataTransferObjectMethod',
    message: 'err.TranslationAlreadyExists',
)]
abstract class TranslationDataTransferObject implements UniqueDataTransferObjectInterface
{
    #[Assert\Valid]
    #[Assert\NotBlank(message: 'err.FieldIsRequired')]
    public ?TranslationDomain $domain;

    #[Assert\Valid]
    #[Assert\NotBlank(message: 'err.FieldIsRequired')]
    public ?TranslationKey $key;

    #[Assert\NotBlank(message: 'err.FieldIsRequired')]
    public ?Locale $locale;

    #[Assert\NotBlank(message: 'err.FieldIsRequired')]
    public ?string $value;

    public ?string $source;

    public function __construct(protected ?Translation $translationEntity = null)
    {
        $this->domain = $translationEntity?->domain;
        $this->key = $translationEntity?->key;
        $this->locale = $translationEntity?->locale;
        $this->value = $translationEntity?->value;
        $this->source = $translationEntity?->source;
    }

    #[\Override]
    public function hasEntity(): bool
    {
        return $this->translationEntity !== null;
    }

    #[\Override]
    public function getEntity(): Translation
    {
        return $this->translationEntity;
    }
}
