<?php

declare(strict_types=1);

namespace ForkCMS\Modules\Backend\Backend\Widgets;

use ForkCMS\Modules\Backend\Domain\Widget\WidgetControllerInterface;
use Symfony\Component\HttpFoundation\Request;
use Twig\Environment as Twig;

final class UserStatistics implements WidgetControllerInterface
{
    public function __construct(private Twig $twig)
    {
    }

    #[\Override]
    public function __invoke(Request $request): string
    {
        return $this->twig->render('@Backend/Backend/Widgets/UserStatistics.html.twig');
    }
}
