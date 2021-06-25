<?php

namespace ForkCMS\Modules\Internationalisation\Domain\Translation;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use ForkCMS\Modules\Internationalisation\Domain\Locale\Locale;
use Symfony\Component\Translation\TranslatableMessage;

/**
 * @ORM\Entity(repositoryClass="ForkCMS\Modules\Internationalisation\Domain\Translation\TranslationRepository")
 * @ORM\Table(
 *     name="translations",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="unique_translation",columns={"domain_application", "domain_moduleName", "key_type", "key_name", "locale"})}
 * )
 * @ORM\HasLifecycleCallbacks
 */
class Translation
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private int $id;

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
        string $value
    ) {
        $this->domain = $domain;
        $this->key = $key;
        $this->locale = $locale;
        $this->value = $value;
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

    public function getId(): int
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

    public function getTranslatable(array $parameters = []): TranslatableMessage
    {
        return new TranslatableMessage($this->key->__toString(), $parameters, $this->domain->__toString());
    }
}
