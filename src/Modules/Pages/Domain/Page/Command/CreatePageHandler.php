<?php

namespace ForkCMS\Modules\Pages\Domain\Page\Command;

use ForkCMS\Modules\Authentication\Backend\Domain\Authentication\Authentication;
use ForkCMS\Modules\Internationalisation\Backend\Domain\Locale\Locale;
use ForkCMS\Modules\Pages\Domain\Page\Page;
use ForkCMS\Modules\Pages\Domain\Page\PageRepository;
use ForkCMS\Modules\Pages\Domain\PageBlock\PageBlock;
use ForkCMS\Modules\Pages\Domain\PageBlock\PageBlockDataTransferObject;
use ForkCMS\Modules\Pages\Domain\PageBlock\PageBlockRepository;
use ForkCMS\Modules\Pages\Backend\Helper\Model as BackendPagesModel;
use ForkCMS\Modules\Search\Backend\Helper\Model as BackendSearchModel;
use ForkCMS\Modules\Tags\Backend\Helper\Model as BackendTagsModel;

final class CreatePageHandler
{
    /** @var PageRepository */
    private $pageRepository;

    /** @var PageBlockRepository */
    private $pageBlockRepository;

    public function __construct(PageRepository $pageRepository, PageBlockRepository $pageBlockRepository)
    {
        $this->pageRepository = $pageRepository;
        $this->pageBlockRepository = $pageBlockRepository;
    }

    public function handle(CreatePage $createPage): void
    {
        $locale = $createPage->locale ?? Locale::workingLocale();
        $createPage->id = $this->pageRepository->getMaximumPageId($locale, Authentication::getUser()->isGod()) + 1;
        $createPage->userId = Authentication::getUser()->getUserId();
        $createPage->sequence = $this->pageRepository->getMaximumSequence(
            $createPage->getParentId(),
            $createPage->locale
        );

        $page = Page::fromDataTransferObject($createPage);

        $this->pageRepository->add($page);
        $this->pageRepository->save($page);
        $this->saveBlocks($page, $createPage);
        $this->saveTags($page, $createPage);

        BackendPagesModel::buildCache(Locale::workingLocale());

        if ($page->getStatus()->isActive()) {
            $data = $page->getData();
            $this->saveSearchIndex(
                ($data['remove_from_search_index'] ?? false)
                || ($data['internal_redirect']['page_id'] ?? false)
                || ($data['external_redirect']['url'] ?? false),
                $page
            );
        }
    }

    private function saveSearchIndex(bool $removeFromSearchIndex, Page $page): void
    {
        if ($removeFromSearchIndex) {
            BackendSearchModel::removeIndex(
                'Pages',
                $page->getId()
            );

            return;
        }

        $searchText = '';
        /*
         * @TODO fix this with the new editor
        foreach ($this->blocksContent as $block) {
            $searchText .= ' ' . $block['html'];
        }
        */

        BackendSearchModel::saveIndex(
            'Pages',
            $page->getId(),
            ['title' => $page->getTitle(), 'text' => $searchText]
        );
    }

    private function saveBlocks(Page $page, CreatePage $createPage): void
    {
        foreach ($createPage->blocks as $position => $blocks) {
            /** @var PageBlockDataTransferObject $blockDataTransferObject */
            foreach ($blocks as $blockDataTransferObject) {
                $blockDataTransferObject->page = $page;
                $blockDataTransferObject->position = $position;
                $pageBlock = PageBlock::fromDataTransferObject($blockDataTransferObject);
                $this->pageBlockRepository->add($pageBlock);
                $this->pageBlockRepository->save($pageBlock);
            }
        }
    }

    private function saveTags(Page $page, CreatePage $createPage): void
    {
        if (!Authentication::isAllowedAction('Edit', 'Tags')
            || !Authentication::isAllowedAction('GetAllTags', 'Tags')) {
            return;
        }

        BackendTagsModel::saveTags(
            $page->getId(),
            $createPage->tags,
            'Pages'
        );
    }
}
