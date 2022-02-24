<?php

namespace ForkCMS\Modules\Internationalisation\Domain\Translation;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ForkCMS\Modules\Backend\Domain\User\Blameable;
use ForkCMS\Modules\Internationalisation\Domain\Locale\Locale;
use Symfony\Component\Translation\TranslatableMessage;

#[ORM\Entity(repositoryClass: TranslationRepository::class)]
#[ORM\Table(name: "internationalisation__translation")]
class Translation
{
    use Blameable;

    #[ORM\Id]
    #[ORM\Column(type: Types::STRING, length: 32, unique: true)]
    private string $id;

    #[ORM\Embedded(class: TranslationDomain::class)]
    private TranslationDomain $domain;

    #[ORM\Embedded(class: TranslationKey::class)]
    private TranslationKey $key;

    #[ORM\Column(type: Types::STRING, length: 5, enumType: Locale::class)]
    private Locale $locale;

    #[ORM\Column(type: Types::TEXT)]
    private string $value;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $source;

    public function __construct(
        TranslationDomain $domain,
        TranslationKey $key,
        Locale $locale,
        string $value,
        string $source = null,
    ) {
        $this->id = md5(implode('$', [$domain, $locale->value, $key]));
        $this->domain = $domain;
        $this->key = $key;
        $this->locale = $locale;
        $this->value = $value;
        $this->source = $source;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getDomain(): TranslationDomain
    {
        return $this->domain;
    }

    public function getKey(): TranslationKey
    {
        return $this->key;
    }

    public function getLocale(): Locale
    {
        return $this->locale;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getCreatedOn(): DateTimeImmutable
    {
        return $this->createdOn;
    }

    public function getCreatedBy(): int
    {
        return $this->createdBy;
    }

    public function getEditedOn(): DateTimeImmutable
    {
        return $this->editedOn;
    }

    public function getEditedBy(): int
    {
        return $this->editedBy;
    }

    /** @param array<int, mixed> $parameters */
    public function getTranslatable(array $parameters = []): TranslatableMessage
    {
        return new TranslatableMessage($this->key->__toString(), $parameters, $this->domain->__toString());
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function change(string $value): void
    {
        $this->value = $value;
    }
}
