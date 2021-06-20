<?php

namespace ForkCMS\Modules\Mailmotor\Backend\Actions;

use ForkCMS\Core\Backend\Domain\Action\ActionIndex;
use ForkCMS\Core\Backend\Helper\Model;
use ForkCMS\Modules\Mailmotor\Domain\Settings\Command\SaveSettings;
use ForkCMS\Modules\Mailmotor\Domain\Settings\Event\SettingsSavedEvent;
use ForkCMS\Modules\Mailmotor\Domain\Settings\SettingsType;
use ForkCMS\Modules\Extensions\Domain\ModuleSetting\ModuleSettingRepository;

/**
 * This is the settings-action (default),
 * it will be used to couple your "mail-engine" account
 */
final class Settings extends ActionIndex
{
    public function execute(): void
    {
        parent::execute();

        $form = $this->createForm(
            SettingsType::class,
            new SaveSettings($this->get(ModuleSettingRepository::class))
        );

        $form->handleRequest($this->getRequest());

        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->template->assign('form', $form->createView());

            $this->parse();
            $this->display();

            return;
        }

        /** @var SaveSettings $settings */
        $settings = $form->getData();

        // The command bus will handle the saving of the settings in the database.
        $this->get('command_bus.public')->handle($settings);

        $this->get('event_dispatcher')->dispatch(
            SettingsSavedEvent::EVENT_NAME,
            new SettingsSavedEvent($settings)
        );

        $redirectAction = $settings->mailEngine === 'not_implemented' ? 'Settings' : 'Ping';

        $this->redirect(
            Model::createUrlForAction(
                $redirectAction,
                null,
                null,
                [
                    'report' => 'saved',
                ]
            )
        );
    }
}
