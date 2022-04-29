<?php

namespace ForkCMS\Modules\Pages\Controller;

use ForkCMS\Modules\Internationalisation\Domain\Locale\InstalledLocaleRepository;
use ForkCMS\Modules\Pages\Domain\Revision\Revision;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

final class PageController
{
    public function __construct(
        private readonly InstalledLocaleRepository $installedLocaleRepository,
        private readonly RouterInterface $router
    ) {
    }

    public function __invoke(Request $request, Revision $revision): Response
    {
        return new Response(
            '<html><head><title>' . $revision->getTitle() . '</title></head><body>' . $revision->getContent() . '</body></html>',
            Response::HTTP_OK,
            ['Content-Type' => 'text/html']
        );
    }
}
