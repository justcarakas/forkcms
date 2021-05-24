<?php

namespace ForkCMS\Modules\Search\Frontend\Widgets;

use ForkCMS\Core\Frontend\Helper\Base\Widget as FrontendBaseWidget;
use ForkCMS\Core\Frontend\Helper\Form as FrontendForm;
use ForkCMS\Core\Frontend\Helper\Navigation as FrontendNavigation;

/**
 * This is a widget with the search form
 */
class Form extends FrontendBaseWidget
{
    public function execute(): void
    {
        parent::execute();
        $this->loadTemplate();

        $form = new FrontendForm('search', FrontendNavigation::getUrlForBlock('Search'), 'get', null, false);
        $form->setParameter('class', 'form-inline my-2 my-lg-0 ml-2');
        $form->setParameter('role', 'search');
        $form->addText('q_widget')->setAttributes(
            [
                'itemprop' => 'query-input',
                'data-role' => 'fork-widget-search-field',
            ]
        );
        $form->parse($this->template);
    }
}
