<?php

declare(strict_types=1);

namespace ForkCMS\Core\Domain\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

/** @extends AbstractType<string> */
final class TogglePasswordType extends AbstractType
{
    #[\Override]
    public function getParent(): string
    {
        return PasswordType::class;
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'toggle_password';
    }
}
