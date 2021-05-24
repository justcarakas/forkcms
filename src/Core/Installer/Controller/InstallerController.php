<?php

namespace ForkCMS\Core\Installer\Controller;

use ForkCMS\Core\Common\Exception\ExitException;
use ForkCMS\Core\Installer\Domain\Installer\ForkInstaller;
use ForkCMS\Core\Installer\Domain\Installer\InstallationData;
use ForkCMS\Core\Installer\Domain\Requirement\RequirementsChecker;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class InstallerController extends AbstractController
{
    public function step2Action(Request $request): Response
    {
        return $this->handleInstallationStep(2, LanguagesType::class, new LanguagesHandler(), $request);
    }

    public function step3Action(Request $request): Response
    {
        return $this->handleInstallationStep(3, ModulesType::class, new ModulesHandler(), $request);
    }

    public function step4Action(Request $request): Response
    {
        return $this->handleInstallationStep(4, DatabaseType::class, new DatabaseHandler(), $request);
    }

    public function step5Action(Request $request): Response
    {
        return $this->handleInstallationStep(5, LoginType::class, new LoginHandler(), $request);
    }

    public function step6Action(Request $request): Response
    {
        $this->checkInstall();

        $status = $this->installer->install($this->getInstallationData($request));

        return $this->render(
            '@ForkCMSInstaller/Installer/step6.html.twig',
            [
                'installStatus' => $status,
                'installer' => $this->installer,
                'data' => $this->getInstallationData($request),
            ]
        );
    }

    /**
     * @throws ExitException if fork is already installed
     */
    protected function checkInstall()
    {
        $filesystem = new Filesystem();
        $kernelDir = $this->getParameter('kernel.project_dir') . '/app';
        $parameterFile = $kernelDir . 'config/parameters.yaml';
        if ($filesystem->exists($parameterFile)) {
            throw new ExitException(
                'This Fork has already been installed. To reinstall, delete
                 parameters.yaml from the ' . $kernelDir . 'config/ directory.',
                'This Fork has already been installed. To reinstall, delete
                 parameters.yaml from the ' . $kernelDir . 'config/ directory. To log in,
                 <a href="/private">click here</a>.',
                Response::HTTP_FORBIDDEN
            );
        }
    }
}
