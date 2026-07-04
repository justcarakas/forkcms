<?php

declare(strict_types=1);

namespace ForkCMS\Modules\ContentBlocks\Frontend\Widgets;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use ForkCMS\Modules\ContentBlocks\Domain\ContentBlock\ContentBlockRepository;
use ForkCMS\Modules\ContentBlocks\Domain\ContentBlock\Revision;
use ForkCMS\Modules\Frontend\Domain\Block\BlockServices;
use ForkCMS\Modules\Frontend\Domain\Widget\AbstractWidgetController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ContentBlock extends AbstractWidgetController
{
    public function __construct(
        BlockServices $blockServices,
        private readonly ContentBlockRepository $contentBlockRepository,
    ) {
        parent::__construct($blockServices);
    }

    #[\Override]
    protected function execute(Request $request, Response $response): void
    {
        try {
            $contentBlock = $this->contentBlockRepository->findForWidget($this->block);
        } catch (NonUniqueResultException | NoResultException) {
            $this->changeTemplatePath(Revision::DEFAULT_TEMPLATE);

            return;
        }
        $revision = $contentBlock->getActiveRevision();

        $this->changeTemplatePath($revision->template);
        $this->assign('content_block_revision', $revision);
    }
}
