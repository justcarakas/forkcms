<?php

namespace ForkCMS\Core\Frontend\Helper;

use ForkCMS\Core\Common\ApplicationInterface;
use ForkCMS\Core\Domain\Kernel\KernelLoader;
use ForkCMS\Core\Frontend\Helper\Base\AjaxAction as FrontendBaseAJAXAction;
use ForkCMS\Modules\Internationalisation\Frontend\Domain\Translator\Language;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * FrontendAJAX
 * This class will handle AJAX-related stuff
 */
class Ajax extends KernelLoader implements ApplicationInterface
{
    /**
     * The action
     *
     * @var string
     */
    private $action;

    /**
     * @var AjaxAction|FrontendBaseAJAXAction
     */
    private $ajaxAction;

    /**
     * The language
     *
     * @var string
     */
    private $language;

    /**
     * The module
     *
     * @var string
     */
    private $module;

    /**
     * Output generated by the action.
     *
     * @var Response
     */
    private $response;

    public function display(): Response
    {
        return $this->response;
    }

    private function splitUpForkData(array $forkData): array
    {
        return [
            $forkData['module'] ?? '',
            $forkData['action'] ?? '',
            $forkData['language'] ?? '',
        ];
    }

    /**
     * This method exists because the service container needs to be set before
     * the request's functionality gets loaded.
     */
    public function initialize(): void
    {
        [$module, $action, $language] = $this->splitUpForkData($this->getForkData());

        if (empty($language)) {
            $language = $this->getContainer()->getParameter('site.default_language');
        }

        try {
            $this->setModule($module);
            $this->setAction($action);
            $this->setLanguage($language);

            if (extension_loaded('newrelic')) {
                newrelic_name_transaction('ajax::' . $module . '::' . $action);
            }

            $this->ajaxAction = new AjaxAction($this->getKernel(), $this->getAction(), $this->getModule());
            $this->response = $this->ajaxAction->getContent();
        } catch (BadRequestHttpException | AccessDeniedHttpException $httpException) {
            $this->ajaxAction = new FrontendBaseAJAXAction($this->getKernel(), '', '');
            $this->ajaxAction->output(
                $httpException->getStatusCode(),
                null,
                $httpException->getMessage()
            );
            $this->response = $this->ajaxAction->getContent();
        } catch (\Exception $exception) {
            $this->ajaxAction = new FrontendBaseAJAXAction($this->getKernel(), '', '');
            $this->ajaxAction->output(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                null,
                $this->getMessageFromException($exception)
            );
            $this->response = $this->ajaxAction->getContent();
        }
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getModule(): string
    {
        if ($this->module === null) {
            throw new BadRequestHttpException('Module has not yet been set.');
        }

        return $this->module;
    }

    public function setAction(string $action): void
    {
        $ajaxActionClass = 'Frontend\\Modules\\' . $this->getModule() . '\\Ajax\\' . $action;
        if (!class_exists($ajaxActionClass)) {
            throw new BadRequestHttpException('Action class ' . $ajaxActionClass . ' does not exist');
        }

        $this->action = $action;
    }

    /**
     * Set the language
     *
     * @param string $language The (interface-)language, will be used to parse labels.
     *
     * @throws Exception
     */
    public function setLanguage(string $language): void
    {
        $possibleLanguages = Language::getActiveLanguages();

        if (!in_array($language, $possibleLanguages)) {
            if (count($possibleLanguages) !== 1 && Model::getContainer()->getParameter('site.multilanguage')) {
                // multiple languages available but none selected
                throw new Exception('Language invalid.');
            }

            $language = array_shift($possibleLanguages);
        }

        $this->language = $language;

        defined('FRONTEND_LANGUAGE') || define('FRONTEND_LANGUAGE', $this->language);
        defined('LANGUAGE') || define('LANGUAGE', $this->language);
        Language::setLocale($this->language);
    }

    public function setModule(string $module): void
    {
        if (!in_array($module, Model::getModules())) {
            throw new AccessDeniedHttpException('Module not allowed');
        }

        $this->module = $module;
    }

    private function getForkData(): array
    {
        $request = Model::getRequest();

        if ($request->request->has('fork')) {
            return (array) $request->request->get('fork');
        }

        if ($request->query->has('fork')) {
            return (array) $request->query->get('fork');
        }

        return (array) $request->query->all();
    }

    private function getMessageFromException(\Exception $exception): string
    {
        if ($this->getContainer()->getParameter('kernel.debug')) {
            return $exception->getMessage();
        }

        return $this->getContainer()->getParameter('fork.debug_message');
    }
}
