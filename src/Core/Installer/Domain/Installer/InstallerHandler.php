<?php

namespace ForkCMS\Core\Installer\Domain\Installer;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Base class to validate and save data from the installer form
 */
abstract class InstallerHandler
{
    final public function process(FormInterface $form, Request $request): bool
    {
        if (!$request->isMethod('POST')) {
            return false;
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $request->getSession()->set('installation_data', $this->processInstallationData($form->getData()));

            return true;
        }

        return false;
    }

    abstract public function processInstallationData(InstallationData $installationData): InstallationData;
}
