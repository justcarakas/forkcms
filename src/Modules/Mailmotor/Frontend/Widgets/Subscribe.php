<?php

namespace ForkCMS\Modules\Mailmotor\Frontend\Widgets;

use ForkCMS\Core\Frontend\Helper\Base\Widget as FrontendBaseWidget;
use ForkCMS\Modules\Mailmotor\Domain\Subscription\Command\Subscription;
use ForkCMS\Modules\Locale\Frontend\Domain\Locale\Locale;
use ForkCMS\Modules\Mailmotor\Domain\Subscription\SubscribeType;

/**
 * This is a widget with the Subscribe form
 */
class Subscribe extends FrontendBaseWidget
{
    public function execute(): void
    {
        parent::execute();
        $this->loadTemplate();
        $form = $this->createForm(
            SubscribeType::class,
            new Subscription(Locale::frontendLanguage())
        );

        $form->handleRequest($this->getRequest());

        if ($form->isSubmitted() && $form->isValid()) {
            return;
        }

        $this->template->assign('form', $form->createView());

        if ($form->isSubmitted()) {
            $this->template->assign('mailmotorSubscribeHasFormError', true);
        }
    }
}
