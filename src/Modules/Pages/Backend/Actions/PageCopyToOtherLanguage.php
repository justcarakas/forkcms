<?php

namespace ForkCMS\Modules\Pages\Backend\Actions;

use ForkCMS\Core\Backend\Domain\Action\ActionIndex as BackendBaseActionIndex;
use ForkCMS\Core\Backend\Exception as BackendException;
use ForkCMS\Core\Backend\Helper\Model as BackendModel;
use ForkCMS\Modules\Locale\Backend\Domain\Locale\Locale;
use ForkCMS\Modules\Pages\Domain\Page\CopyPageDataTransferObject;
use ForkCMS\Modules\Pages\Domain\Page\Form\CopyPageToOtherLanguageType;
use ForkCMS\Modules\Pages\Domain\Page\PageRepository;
use Exception;
use ForkCMS\Core\Common\ForkCMS\Utility\Module\CopyContentToOtherLocale\CopyContentFromModulesToOtherLocaleManager;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * BackendPagesCopy
 * This is the copy-action, it will copy pages from one language to another
 * Remark :    IMPORTANT existing data will be removed, this feature is also experimental!
 */
class PageCopyToOtherLanguage extends BackendBaseActionIndex
{
    /** @var CopyContentFromModulesToOtherLocaleManager */
    private $copyManager;

    public function setKernel(KernelInterface $kernel = null): void
    {
        parent::setKernel($kernel);

        $this->copyManager = $this->getContainer()->get(CopyContentFromModulesToOtherLocaleManager::class);
    }

    public function execute(): void
    {
        // call parent, this will probably add some general CSS/JS or other required files
        parent::execute();

        $form = $this->createForm(CopyPageToOtherLanguageType::class, new CopyPageDataTransferObject());
        $form->handleRequest($this->getRequest());

        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->redirect(BackendModel::createUrlForAction('PageIndex') . '&error=error-copy');
        }

        /** @var CopyPageDataTransferObject $data */
        $data = $form->getData();

        // validate
        if ($data->from === null) {
            throw new BackendException('Specify a from parameter.');
        }
        if ($data->to === null) {
            throw new BackendException('Specify a to parameter.');
        }

        // copy pages
        try {
            $this->copyManager->copyOne(
                $data->pageToCopy,
                Locale::fromString($data->from),
                Locale::fromString($data->to)
            );
        } catch (Exception $e) {
            $this->redirect(
                BackendModel::createUrlForAction('PageEdit')
                . '&id='
                . $data->pageToCopy->getId()
                . '&error=error-copy'
            );
        }

        // redirect
        $this->redirect(
            BackendModel::createUrlForAction('PageIndex') . '&report=copy-added&var=' . rawurlencode($data->to)
        );
    }
}
