<?php

namespace ForkCMS\Modules\ContentBlocks\Backend\Actions;

use ForkCMS\Modules\Authentication\Backend\Domain\Authentication\Authentication;
use ForkCMS\Core\Backend\Domain\Action\ActionEdit as BackendBaseActionEdit;
use ForkCMS\Core\Backend\Helper\Model as BackendModel;
use ForkCMS\Modules\Internationalisation\Backend\Domain\Locale\Locale;
use ForkCMS\Core\Backend\Domain\Form\DeleteType;
use ForkCMS\Modules\ContentBlocks\Domain\ContentBlock\Command\UpdateContentBlock;
use ForkCMS\Modules\ContentBlocks\Domain\ContentBlock\ContentBlock;
use ForkCMS\Modules\ContentBlocks\Domain\ContentBlock\ContentBlockRepository;
use ForkCMS\Modules\ContentBlocks\Domain\ContentBlock\ContentBlockRevisionDataGrid;
use ForkCMS\Modules\ContentBlocks\Domain\ContentBlock\ContentBlockType;
use ForkCMS\Modules\ContentBlocks\Domain\ContentBlock\Event\ContentBlockUpdated;
use ForkCMS\Modules\ContentBlocks\Domain\ContentBlock\Exception\ContentBlockNotFound;
use ForkCMS\Modules\Extensions\Domain\ModuleSetting\ModuleSettingRepository;
use Symfony\Component\Form\Form;

/**
 * This is the edit-action, it will display a form to edit an existing item
 */
class Edit extends BackendBaseActionEdit
{
    public function execute(): void
    {
        parent::execute();

        $contentBlock = $this->getContentBlock();

        $form = $this->getForm($contentBlock);

        $deleteForm = $this->createForm(
            DeleteType::class,
            ['id' => $contentBlock->getId()],
            ['module' => $this->getModule()]
        );
        $this->template->assign('deleteForm', $deleteForm->createView());

        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->template->assign('form', $form->createView());
            $this->template->assign('contentBlock', $contentBlock);
            $this->template->assign('revisions', ContentBlockRevisionDataGrid::getHtml($contentBlock, Locale::workingLocale()));
            $this->header->appendDetailToBreadcrumbs($contentBlock->getTitle());

            $this->parse();
            $this->display();

            return;
        }

        /** @var UpdateContentBlock $updateContentBlock */
        $updateContentBlock = $this->updateContentBlock($form);

        $this->get('event_dispatcher')->dispatch(
            ContentBlockUpdated::EVENT_NAME,
            new ContentBlockUpdated($updateContentBlock->getContentBlockEntity())
        );

        $this->redirect(
            $this->getBackLink(
                [
                    'report' => 'edited',
                    'var' => $updateContentBlock->title,
                    'highlight' => 'row-' . $contentBlock->getId(),
                ]
            )
        );
    }

    private function getBackLink(array $parameters = []): string
    {
        return BackendModel::createUrlForAction(
            'Index',
            null,
            null,
            $parameters
        );
    }

    private function getContentBlock(): ContentBlock
    {
        /** @var ContentBlockRepository $contentBlockRepository */
        $contentBlockRepository = $this->get(ContentBlockRepository::class);

        // specific revision?
        $revisionId = $this->getRequest()->query->getInt('revision');

        if ($revisionId !== 0) {
            $this->template->assign('usingRevision', true);

            try {
                return $contentBlockRepository->findOneByRevisionIdAndLocale($revisionId, Locale::workingLocale());
            } catch (ContentBlockNotFound $e) {
                $this->redirect($this->getBackLink(['error' => 'non-existing']));
            }
        }

        try {
            return $contentBlockRepository->findOneByIdAndLocale(
                $this->getRequest()->query->getInt('id'),
                Locale::workingLocale()
            );
        } catch (ContentBlockNotFound $e) {
            $this->redirect($this->getBackLink(['error' => 'non-existing']));
        }
    }

    private function getForm(ContentBlock $contentBlock): Form
    {
        $form = $this->createForm(
            ContentBlockType::class,
            new UpdateContentBlock($contentBlock),
            [
                'theme' => $this->get(ModuleSettingRepository::class)->get('Core', 'theme', 'Fork'),
            ]
        );

        $form->handleRequest($this->getRequest());

        return $form;
    }

    private function updateContentBlock(Form $form): UpdateContentBlock
    {
        /** @var UpdateContentBlock $updateContentBlock */
        $updateContentBlock = $form->getData();
        $updateContentBlock->userId = Authentication::getUser()->getUserId();

        // The command bus will handle the saving of the content block in the database.
        $this->get('command_bus.public')->handle($updateContentBlock);

        return $updateContentBlock;
    }
}
