<?php

namespace ForkCMS\Modules\Pages\Controller;

use ForkCMS\Modules\Frontend\Domain\Block\Block;
use ForkCMS\Modules\Frontend\Domain\Block\BlockControllerInterface;
use ForkCMS\Modules\Pages\Domain\Revision\Revision;
use ForkCMS\Modules\Pages\Domain\RevisionBlock\RevisionBlock;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

final class PageController
{
    public function __construct(
        private readonly ServiceLocator $frontendBlocks,
        private readonly SerializerInterface $serializer,
    ) {
    }

    public function __invoke(Request $request, Revision $revision): Response
    {
        $revisionContext = [
            'title' => $revision->getTitle(),
            'positions' => [],
        ];

        $response = $request->getPreferredFormat() === 'json' ? new JsonResponse() : new Response();

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
                        $revisionContext['positions'][$position][] = [
                            'block' => $blockName,
                            'content' => $blockController($request, $response),
                        ];
                        $responseOverride = $blockController->getResponseOverride();
                        if ($responseOverride !== null) {
                            return $responseOverride;
                        }
                    }
                }
            }
        }

        if ($response instanceof JsonResponse) {
            $response->setJson($this->serializer->serialize($revisionContext, 'json'));

            return $response;
        }

        $content = '<html><head><title>' . $revisionContext['title'] . '</title></head><body>';
        $content .= '<h1>' . $revisionContext['title'] . '</h1>';
        foreach ($revisionContext['positions'] as $position => $blocks) {
            $content .= '<h2>' . $position . '</h2>';
            foreach ($blocks as $block) {
                $content .= '<div class="block">' . $block['content'] . '</div>';
            }
        }
        $content .= '</body></html>';
        $response->setContent($content);

        return $response;
    }
}
