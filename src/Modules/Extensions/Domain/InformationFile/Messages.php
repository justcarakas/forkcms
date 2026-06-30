<?php

namespace ForkCMS\Modules\Extensions\Domain\InformationFile;

use ForkCMS\Modules\Internationalisation\Domain\Translation\TranslationKey;
use ForkCMS\Modules\Internationalisation\Domain\Translation\Type;

final class Messages
{
    /** @param TranslationKey[] $messages */
    public function __construct(private(set) array $messages = [])
    {
    }

    public function addMessage(TranslationKey $message): void
    {
        $this->messages[$message->name] = $message;
    }

    public function hasErrors(): bool
    {
        return count(
            array_filter(
                $this->messages,
                static fn (TranslationKey $translationKey): bool => $translationKey->type === Type::ERROR
            )
        ) === 0;
    }
}
