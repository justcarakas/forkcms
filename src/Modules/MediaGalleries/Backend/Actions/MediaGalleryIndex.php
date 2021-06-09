<?php

namespace ForkCMS\Modules\MediaGalleries\Backend\Actions;

use ForkCMS\Core\Backend\Domain\Action\ActionIndex as BackendBaseActionIndex;
use ForkCMS\Modules\Internationalisation\Backend\Domain\Translator\Language;
use ForkCMS\Core\Backend\Helper\Model as BackendModel;
use ForkCMS\Modules\MediaGalleries\Domain\MediaGallery\MediaGalleryDataGrid;
use ForkCMS\Modules\MediaLibrary\Domain\MediaGroup\TypeType;

/**
 * This is the class to Show all MediaGallery Entities
 */
class MediaGalleryIndex extends BackendBaseActionIndex
{
    public function execute(): void
    {
        parent::execute();

        $form = $this->createForm(
            TypeType::class,
            null,
            [
                'action' => BackendModel::createUrlForAction('MediaGalleryAdd'),
                'method' => 'GET',
            ]
        );

        $this->template->assign('warnings', self::getWarnings());
        $this->template->assign('dataGrid', MediaGalleryDataGrid::getHtml());
        $this->template->assign('form', $form->createView());

        $this->parse();
        $this->display();
    }

    public static function getWarnings(): array
    {
        // MediaLibrary "Index" action should be allowed
        if (BackendModel::isModuleInstalled('MediaLibrary')) {
            return [];
        }

        // Add warning
        return [
            [
                'message' => sprintf(
                    Language::err('MediaLibraryModuleRequired', 'MediaGalleries'),
                    BackendModel::createUrlForAction('Modules', 'Extensions')
                ),
            ],
        ];
    }
}
