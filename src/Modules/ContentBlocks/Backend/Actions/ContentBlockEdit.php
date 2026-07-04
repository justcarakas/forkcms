<?php

declare(strict_types=1);

namespace ForkCMS\Modules\ContentBlocks\Backend\Actions;

use ForkCMS\Core\Domain\Header\Breadcrumb\Breadcrumb;
use ForkCMS\Core\Domain\Header\FlashMessage\FlashMessage;
use ForkCMS\Modules\Backend\Domain\Action\AbstractFormActionController;
use ForkCMS\Modules\Backend\Domain\Action\ActionServices;
use ForkCMS\Modules\Backend\Domain\Action\ActionSlug;
use ForkCMS\Modules\ContentBlocks\Domain\ContentBlock\Command\CreateContentBlockRevision;
use ForkCMS\Modules\ContentBlocks\Domain\ContentBlock\ContentBlock;
use ForkCMS\Modules\ContentBlocks\Domain\ContentBlock\ContentBlockRepository;
use ForkCMS\Modules\ContentBlocks\Domain\ContentBlock\ContentBlockType;
use ForkCMS\Modules\ContentBlocks\Domain\ContentBlock\Revision;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Edit an existing content block.
 */
final class ContentBlockEdit extends AbstractFormActionController
{
    public function __construct(
        ActionServices $actionServices,
        private readonly ContentBlockRepository $contentBlockRepository,
    ) {
        parent::__construct($actionServices);
    }

    #[\Override]
    protected function getFormResponse(Request $request): ?Response
    {
        $contentBlock = $this->getEntityFromRequest($request, ContentBlock::class);
        $revisionId = $request->query->getInt('revision');
        $revision = $revisionId === 0 ? $contentBlock->getActiveRevision() : $contentBlock->revisions->get($revisionId);
        if (!$revision instanceof Revision) {
            throw new NotFoundHttpException('Revision not found');
        }
        $this->assign('usingRevision', $revision->archivedOn !== null);

        $this->header->addBreadcrumb(new Breadcrumb($revision->title));

        if (!$this->contentBlockRepository->isContentBlockInUse($contentBlock)) {
            $this->addDeleteForm(
                ['id' => $contentBlock->id],
                ActionSlug::fromFQCN(ContentBlockDelete::class)
            );
        }

        return $this->handleForm(
            request: $request,
            formType: ContentBlockType::class,
            formData: CreateContentBlockRevision::fromRevision($revision),
            redirectResponse: new RedirectResponse(ContentBlockIndex::getActionSlug()->generateRoute($this->router)),
            formOptions: ['showRevisionsForContentBlockId' => $contentBlock->id],
            successFlashMessageCallback: static function (FormInterface $form): FlashMessage {
                return FlashMessage::success('Edited', ['%contentBlock%' => $form->getData()->title]);
            }
        );
    }
}
