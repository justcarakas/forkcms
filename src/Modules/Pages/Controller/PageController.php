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
        $content = '<html><head><title>' . $revision->getTitle() . '</title></head><body>';
        $content .= '<ul>';

        $content .= '</ul>';
        $content .= '</body></html>';
        return new Response(
            $content,
            Response::HTTP_OK,
            ['Content-Type' => 'text/html']
        );
    }
}
