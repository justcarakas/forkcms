<?php

declare(strict_types=1);

namespace ForkCMS\Modules\Internationalisation\Domain\Translation;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ForkCMS\Core\Domain\Util\Ensure;
use ForkCMS\Modules\Backend\Domain\User\Blameable;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleName;
use ForkCMS\Modules\Internationalisation\Domain\Locale\Locale;
use InvalidArgumentException;
use Symfony\Component\Translation\TranslatableMessage;

#[ORM\Entity(repositoryClass: TranslationRepository::class)]
class Translation
{
    use Blameable;

    #[ORM\Id]
    #[ORM\Column(type: Types::STRING, length: 32, unique: true)]
    private(set) string $id;

    #[ORM\Column(type: Types::STRING, length: 32, options: ['comment' => 'Translation id across locale'])]
    private(set) string $crossLocaleId;

    #[ORM\Embedded(class: TranslationDomain::class)]
    private(set) TranslationDomain $domain;

    #[ORM\Embedded(class: TranslationKey::class)]
    private(set) TranslationKey $key;

    #[ORM\Column(type: Types::STRING, length: 5, enumType: Locale::class)]
    private(set) Locale $locale;

    #[ORM\Column(type: Types::TEXT)]
    private(set) string $value;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private(set) ?string $source;

    public function __construct(
        TranslationDomain $domain,
        TranslationKey $key,
        Locale $locale,
        string $value,
        ?string $source = null,
    ) {
        if ($domain->moduleName === ModuleName::core()) {
            throw new InvalidArgumentException('Cannot create a translation for the core module');
        }

        $this->id = md5(implode('$', [$domain, $locale->value, $key]));
        $this->crossLocaleId = md5(implode('$', [$domain, $key]));
        $this->domain = $domain;
        $this->key = $key;
        $this->locale = $locale;
        $this->value = $value;
        $this->source = $source;
    }

    /** @param array<int, mixed> $parameters */
    public function getTranslatable(array $parameters = []): TranslatableMessage
    {
        return new TranslatableMessage($this->key->__toString(), $parameters, $this->domain->__toString());
    }

    public function change(string $value): void
    {
        $this->value = $value;
    }

    public static function fromDataTransferObject(TranslationDataTransferObject $dataTransferObject): self
    {
        if ($dataTransferObject->hasEntity()) {
            $translation = $dataTransferObject->getEntity();
            $translation->domain = $dataTransferObject->domain;
            $translation->key = $dataTransferObject->key;
            $translation->locale = $dataTransferObject->locale;
            $translation->value = $dataTransferObject->value;
            $translation->source = $dataTransferObject->source;

            return $translation;
        }

        return new self(
            Ensure::isNotNull($dataTransferObject->domain),
            Ensure::isNotNull($dataTransferObject->key),
            Ensure::isNotNull($dataTransferObject->locale),
            Ensure::isNotNull($dataTransferObject->value),
            $dataTransferObject->source,
        );
    }
}
