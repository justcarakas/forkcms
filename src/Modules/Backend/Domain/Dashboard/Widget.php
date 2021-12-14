<?php

namespace ForkCMS\Modules\Backend\Domain\Dashboard;

use Symfony\Contracts\Translation\TranslatableInterface;

final class Widget
{
    public function __construct(private TranslatableInterface|string $title, private string $content)
    {
    }

    public function getTitle(): TranslatableInterface|string
    {
        return $this->title;
    }

    public function getContent(): string
    {
        return $this->content;
    }
}
