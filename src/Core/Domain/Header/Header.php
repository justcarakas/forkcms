<?php

namespace ForkCMS\Core\Domain\Header;

use ForkCMS\Core\Domain\Header\FlashMessage\FlashMessage;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleName;
use LogicException;
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\KernelInterface;
use Twig\Environment;

/**
 * This class will be used to alter the head-part of the HTML-document that will be created by he Backend
 * Therefore it will handle meta-stuff (title, including JS, including CSS, ...).
 */
final class Header
{
    private JsData $jsData;

    public function __construct(
        private RequestStack $requestStack,
        KernelInterface $kernel,
    ) {
        $this->jsData = new JsData(
            [
                'locale' => $requestStack->getMainRequest()?->getLocale(),
                'default_locale' => $requestStack->getMainRequest()?->getLocale(),
                'debug' => $kernel->isDebug(),
                'session_timeout' => $this->getFirstPossibleSessionTimeout(),
            ]
        );
    }

    public function addJsData(ModuleName $module, string $key, mixed $value): void
    {
        $this->jsData->add($module, $key, $value);
    }

    public function parse(Environment $twig): void
    {
        $twig->addGlobal('jsData', $this->jsData);
    }

    private function getFirstPossibleSessionTimeout(): int
    {
        $garbageCollectionMaxLifeTime = (int) ini_get('session.gc_maxlifetime');
        $cookieLifetime = (int) ini_get('session.cookie_lifetime');

        if ($cookieLifetime === 0 || $cookieLifetime < $garbageCollectionMaxLifeTime) {
            return $garbageCollectionMaxLifeTime;
        }

        return $cookieLifetime;
    }

    public function addFlashMessage(FlashMessage $flashMessage): void
    {
        /** @var Session $session */
        $session = $this->requestStack->getSession();
        try {
            $session->getFlashBag()->add(
                $flashMessage->getType()->value,
                $flashMessage->getMessage()
            );
        } catch (SessionNotFoundException $e) {
            throw new LogicException('You cannot use the addFlash method if sessions are disabled. Enable them in "config/packages/framework.yaml".', 0, $e);
        }
    }
}
