<?php

namespace ForkCMS\Modules\Pages\Controller;

use ForkCMS\Modules\Extensions\Domain\Module\ModuleName;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleSettings;
use ForkCMS\Modules\Frontend\Domain\Block\Block;
use ForkCMS\Modules\Frontend\Domain\Block\BlockControllerInterface;
use ForkCMS\Modules\Pages\Domain\Revision\Revision;
use ForkCMS\Modules\Pages\Domain\RevisionBlock\RevisionBlock;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Twig\Environment;

final class PageController
{
    public function __construct(
        private readonly ServiceLocator $frontendBlocks,
        private readonly SerializerInterface $serializer,
        private readonly Environment $twig,
        private readonly ModuleSettings $moduleSettings
    ) {
    }

    public function __invoke(Request $request, Revision $revision): Response
    {
        $frontendModuleName = ModuleName::fromString('Frontend');
        $revisionContext = [
            'siteTitle' => $this->moduleSettings->get($frontendModuleName, 'site_title_' . $request->getLocale()),
            'positions' => [],
            'template' => $revision->getThemeTemplate()->getTemplatePath(),
        ];

        $hasJsonResponse = $request->getPreferredFormat() === 'json';
        $response = $hasJsonResponse ? new JsonResponse() : new Response();

        /** @var array<string, array<int,RevisionBlock>> $positions */
        $positions = [];
        foreach ($revision->getBlocks() as $revisionBlock) {
            $positions[$revisionBlock->getPosition()][] = $revisionBlock;
        }
        foreach ($positions as $position => $revisionBlocks) {
            $revisionContext['positions'][$position] = [];
            foreach ($revisionBlocks as $revisionBlock) {
                if ($revisionBlock->getBlock() instanceof Block) {
                    $blockName = (string) $revisionBlock->getBlock();
                    if ($this->frontendBlocks->has($blockName)) {
                        /** @var BlockControllerInterface $blockController */
                        $blockController = $this->frontendBlocks->get($blockName);
                        if ($hasJsonResponse) {
                            $revisionContext['positions'][$position][] = [
                                'block' => $blockName,
                                'content' => $blockController($request, $response),
                            ];
                        } else {
                            $revisionContext['positions'][$position][] = $blockController($request, $response);
                        }
                        $responseOverride = $blockController->getResponseOverride();
                        if ($responseOverride !== null) {
                            return $responseOverride;
                        }
                    }
                }
            }
        }

        if ($hasJsonResponse) {
            $response->setJson($this->serializer->serialize($revisionContext, 'json'));

            return $response;
        }

        $response->setContent(
            $this->twig->render(
                $this->twig->createTemplate($this->twig->render('@Pages/_page_blocks.html.twig', $revisionContext)),
                $revisionContext
            )
        );

        return $response;
    }
}
