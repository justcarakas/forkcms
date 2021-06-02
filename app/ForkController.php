<?php

namespace ForkCMS\App;

use Backend\Core\Engine\Ajax as BackendAjax;
use Backend\Core\Engine\Backend;
use Frontend\Core\Engine\Ajax as FrontendAjax;
use Frontend\Core\Engine\Frontend;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Backend\Init as BackendInit;
use Frontend\Init as FrontendInit;
use Common\Exception\RedirectException;
use Symfony\Component\HttpFoundation\Response;
use Twig\Error\Error as TwigError;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Application routing
 */
class ForkController extends AbstractController
{
    public const DEFAULT_APPLICATION = 'Frontend';

    private KernelInterface $kernel;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * Virtual folders mappings
     *
     * @var array
     */
    private static $routes = [
        '' => self::DEFAULT_APPLICATION,
        'private' => 'Backend',
        'Backend' => 'Backend',
        'backend' => 'Backend',
    ];

    /**
     * Get the possible routes
     */
    public static function getRoutes(): array
    {
        return self::$routes;
    }

    /**
     * Runs the backend
     */
    public function backendController(): Response
    {
        $applicationClass = $this->initializeBackend('Backend');
        $application = new $applicationClass($this->kernel);

        return $this->handleApplication($application);
    }

    /**
     * Runs the backend ajax requests
     */
    public function backendAjaxController(): Response
    {
        $applicationClass = $this->initializeBackend('BackendAjax');
        $application = new $applicationClass($this->kernel);

        return $this->handleApplication($application);
    }

    /**
     * Runs the frontend requests
     */
    public function frontendController(): Response
    {
        $applicationClass = $this->initializeFrontend('Frontend');
        $application = new $applicationClass($this->kernel);

        return $this->handleApplication($application);
    }

    /**
     * Runs the frontend ajax requests
     */
    public function frontendAjaxController(): Response
    {
        $applicationClass = $this->initializeFrontend('FrontendAjax');
        $application = new $applicationClass($this->kernel);

        return $this->handleApplication($application);
    }

    /**
     * Runs an application and returns the Response
     */
    protected function handleApplication(ApplicationInterface $application): Response
    {
        $application->passContainerToModels();

        try {
            $application->initialize();

            return $application->display();
        } catch (RedirectException $ex) {
            return $ex->getResponse();
        } catch (TwigError $twigError) {
            if ($twigError->getPrevious() instanceof RedirectException) {
                return $twigError->getPrevious()->getResponse();
            }

            throw $twigError;
        }
    }

    /**
     * @param string $app The name of the application to load (ex. BackendAjax)
     *
     * @return string The name of the application class we need to instantiate.
     */
    protected function initializeBackend(string $app): string
    {
        $init = new BackendInit($this->kernel);
        $init->initialize($app);

        return $app === 'BackendAjax' ? BackendAjax::class : Backend::class;
    }

    /**
     * @param string $app The name of the application to load (ex. frontend_ajax)
     *
     * @return string The name of the application class we need to instantiate.
     */
    protected function initializeFrontend(string $app): string
    {
        $init = new FrontendInit($this->kernel);
        $init->initialize($app);

        return $app === 'FrontendAjax' ? FrontendAjax::class : Frontend::class;
    }
}
