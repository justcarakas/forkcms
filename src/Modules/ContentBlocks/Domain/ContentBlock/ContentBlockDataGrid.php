<?php

namespace ForkCMS\Modules\ContentBlocks\Domain\ContentBlock;

use ForkCMS\Core\Backend\Domain\DataGrid\DataGridDatabase;
use ForkCMS\Core\Backend\Domain\Twig\TemplateModifiers;
use ForkCMS\Modules\Authentication\Backend\Domain\Authentication\Authentication as BackendAuthentication;
use ForkCMS\Core\Backend\Helper\Model;
use ForkCMS\Modules\Locale\Backend\Domain\Translator\Language;
use ForkCMS\Modules\Locale\Backend\Domain\Locale\Locale;
use SpoonFilter;

/**
 * @TODO replace with a doctrine implementation of the data grid
 */
class ContentBlockDataGrid extends DataGridDatabase
{
    public function __construct(Locale $locale)
    {
        parent::__construct(
            'SELECT i.id, i.title, i.hidden
             FROM content_blocks AS i
             WHERE i.status = :active AND i.language = :language',
            ['active' => Status::active(), 'language' => $locale]
        );
        $this->setColumnFunction('htmlspecialchars', ['[title]'], 'title', false);
        $this->setSortingColumns(['title']);

        // show the hidden status
        $this->addColumn('isHidden', SpoonFilter::ucfirst(Language::lbl('VisibleOnSite')), '[hidden]');
        $this->setColumnFunction([TemplateModifiers::class, 'showBool'], ['[hidden]', true], 'isHidden');

        // check if this action is allowed
        if (BackendAuthentication::isAllowedAction('Edit')) {
            $editUrl = Model::createUrlForAction('Edit', null, null, ['id' => '[id]'], false);
            $this->setColumnURL('title', $editUrl);
            $this->addColumn('edit', null, Language::lbl('Edit'), $editUrl, Language::lbl('Edit'));
        }
    }

    public static function getHtml(Locale $locale): string
    {
        return (new self($locale))->getContent();
    }
}
