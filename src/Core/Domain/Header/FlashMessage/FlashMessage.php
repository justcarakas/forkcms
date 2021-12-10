<?php

namespace ForkCMS\Core\Domain\Header\FlashMessage;

use ForkCMS\Modules\Internationalisation\Domain\Translation\TranslationKey;
use ForkCMS\Modules\Internationalisation\Domain\Translation\Type;
use Symfony\Contracts\Translation\TranslatableInterface;

final class FlashMessage
{
    public function __construct(private string|TranslatableInterface $message, private FlashMessageType $type)
    {
    }

    public function getMessage(): string|TranslatableInterface
    {
        return $this->message;
    }

    public function getType(): FlashMessageType
    {
        return $this->type;
    }

    public static function success(string $successMessage, array $parameters = []): self
    {
        return new self(
            TranslationKey::message($successMessage)->withParameters($parameters),
            FlashMessageType::success()
        );
    }

    public static function info(string $infoMessage, array $parameters = []): self
    {
        return new self(TranslationKey::message($infoMessage)->withParameters($parameters), FlashMessageType::info());
    }

    /**
     * @param Type|null $translationType defaults to error
     */
    public static function warning(string $warningMessage, array $parameters = [], Type $translationType = null): self
    {
        return new self(
            TranslationKey::forType($translationType ?? Type::error(), $warningMessage)->withParameters($parameters),
            FlashMessageType::warning()
        );
    }

    public static function error(string $errorMessage, array $parameters = []): self
    {
        return new self(TranslationKey::error($errorMessage)->withParameters($parameters), FlashMessageType::error());
    }
}
