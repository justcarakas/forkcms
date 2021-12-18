<?php

namespace ForkCMS\Modules\Backend\Domain\User\Event;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Contracts\EventDispatcher\Event;

final class BuildUserSettingsFormEvent extends Event
{
    public function __construct(private FormBuilderInterface $formBuilder)
    {
    }

    public function getFormBuilder(): FormBuilderInterface
    {
        return $this->formBuilder;
    }
}
