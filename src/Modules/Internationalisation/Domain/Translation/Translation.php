<?php

namespace ForkCMS\Modules\Internationalisation\Domain\Translation;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use ForkCMS\Modules\Internationalisation\Domain\Locale\Locale;
use Symfony\Component\Translation\TranslatableMessage;

/**
 * @ORM\Entity(repositoryClass="ForkCMS\Modules\Internationalisation\Domain\Translation\TranslationRepository")
 * @ORM\Table(name="translations")
 * @ORM\HasLifecycleCallbacks
 */
class Translation
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", unique=true, length=32)
     */
    private string $id;

    /**
     * @ORM\Embedded(class="TranslationDomain")
     */
    private TranslationDomain $domain;

    /**
     * @ORM\Embedded(class="TranslationKey")
     */
    private TranslationKey $key;

    /**
     * @ORM\Column(type="modules__internationalisation__locale__locale")
     */
    private Locale $locale;

    /**
     * @ORM\Column(type="text")
     */
    private string $value;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $source;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private DateTimeImmutable $createdOn;

    /**
     * @ORM\Column(type="integer")
     */
    private int $createdBy;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private DateTimeImmutable $editedOn;

    /**
     * @ORM\Column(type="integer")
     */
    private int $editedBy;

    public function __construct(
        TranslationDomain $domain,
        TranslationKey $key,
        Locale $locale,
        string $value,
        string $source = null,
    ) {
        $this->id = md5(implode('$', [$domain, $locale, $key]));
        $this->domain = $domain;
        $this->key = $key;
        $this->locale = $locale;
        $this->value = $value;
        $this->source = $source;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist(): void
    {

        $this->createdOn = $this->editedOn = new DateTimeImmutable();
        $this->editedBy = $this->createdBy = 1; //@TODO fix this
    }

    /**
     * @ORM\PostPersist
     */
    public function postPersist(): void
    {
        $this->editedOn = new DateTimeImmutable();
        $this->editedBy = 1; //@TODO fix this
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
