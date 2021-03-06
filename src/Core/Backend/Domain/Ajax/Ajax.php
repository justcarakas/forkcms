<?php

namespace ForkCMS\Core\Backend\Domain\Ajax;

use ForkCMS\Core\Common\ApplicationInterface;
use ForkCMS\Core\Domain\Kernel\KernelLoader;
use Symfony\Component\HttpFoundation\Response;
use ForkCMS\Core\Backend\Domain\Ajax\AjaxAction as BackendBaseAJAXAction;
use ForkCMS\Modules\Internationalisation\Backend\Domain\Translator\Language as BackendLanguage;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * This class will handle AJAX-related stuff
 */
class Ajax extends KernelLoader implements ApplicationInterface
{
    /**
     * @var AjaxActionFactory|BackendBaseAJAXAction
     */
    private $ajaxAction;

    public function display(): Response
    {
        if ($this->ajaxAction instanceof AjaxActionFactory) {
            return $this->ajaxAction->display();
        }

        return $this->ajaxAction->getContent();
    }

    public function initialize(): void
    {
        try {
            // check if the user is logged in
            $this->validateLogin();
        } catch (UnauthorizedHttpException $e) {
            $this->ajaxAction = new BackendBaseAJAXAction($this->getKernel());
            $this->ajaxAction->output(Response::HTTP_UNAUTHORIZED, null, $e->getMessage());

            return;
        }

        // named application
        if (!defined('NAMED_APPLICATION')) {
            // @todo This is being used to generate error URL's and this is maybe not necessary
            define('NAMED_APPLICATION', 'BackendAjax');
        }

        try {
            // process the query string
            $url = new Url($this->getKernel());

            // create a new action
            $this->ajaxAction = new AjaxActionFactory($this->getKernel(), $url->getModule(), $url->getAction());
        } catch (Exception $e) {
            $this->ajaxAction = new BackendBaseAJAXAction($this->getKernel());
            $this->ajaxAction->output(Response::HTTP_INTERNAL_SERVER_ERROR, null, $e->getMessage());
        }
    }

    /**
     * Do authentication stuff
     * This method could end the script by throwing an exception
     */
    private function validateLogin(): void
    {
        // check if the user is logged on, if not he shouldn't load any JS-file
        if (!Authentication::isLoggedIn()) {
            throw new UnauthorizedHttpException('Backend', 'Not logged in.');
        }

        // set interface language
        BackendLanguage::setLocale(Authentication::getUser()->getSetting('interface_language'));
    }
}
