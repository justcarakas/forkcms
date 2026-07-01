<?php

namespace ForkCMS\Modules\Installer\Domain\Requirement;

final readonly class Requirement
{
    private function __construct(
        public string $name,
        public RequirementStatus $status,
        public string $message
    ) {
    }

    public static function check(
        string $name,
        bool $requirementIsMet,
        string $requirementIsMetMessage,
        string $requirementNotMetMessage,
        RequirementStatus $requirementNotMetStatus
    ): self {
        return new self(
            $name,
            $requirementIsMet ? RequirementStatus::SUCCESS : $requirementNotMetStatus,
            $requirementIsMet ? $requirementIsMetMessage : $requirementNotMetMessage
        );
    }
}
