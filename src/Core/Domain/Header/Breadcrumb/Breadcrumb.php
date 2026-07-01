<?php

namespace ForkCMS\Core\Domain\Header\Breadcrumb;

use ForkCMS\Modules\Internationalisation\Domain\Locale\Locale;
use LogicException;
use Stringable;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class Breadcrumb implements Stringable
{
    public function __construct(
        public string|TranslatableInterface $label,
        public ?string $url = null
    ) {
    }

    /**
     * @internal This method is only by the collection to make it easier to add a translation as a label
     */
    public function withTranslatedLabel(TranslatorInterface $translator, ?Locale $locale = null): self
    {
        if ($this->label instanceof TranslatableInterface) {
            return new self(
                $this->label->trans($translator, $locale instanceof Locale ? $locale->value : $locale),
                $this->url,
            );
        }

        return $this;
    }

    public function __toString(): string
    {
        if ($this->label instanceof TranslatorInterface) {
            throw new LogicException(
                'Cannot convert a translated label to string, call the method withTranslatedLabel first'
            );
        }

        return $this->label;
    }
}
