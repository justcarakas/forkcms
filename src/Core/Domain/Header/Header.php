<?php

declare(strict_types=1);

namespace ForkCMS\Core\Domain\Header;

use ForkCMS\Core\Domain\Application\Application;
use ForkCMS\Core\Domain\Header\Asset\Asset;
use ForkCMS\Core\Domain\Header\Asset\AssetCollection;
use ForkCMS\Core\Domain\Header\Asset\Priority;
use ForkCMS\Core\Domain\Header\Breadcrumb\Breadcrumb;
use ForkCMS\Core\Domain\Header\Breadcrumb\BreadcrumbCollection;
use ForkCMS\Core\Domain\Header\FlashMessage\FlashMessage;
use ForkCMS\Core\Domain\Header\Meta\MetaCollection;
use ForkCMS\Core\Domain\Header\Meta\MetaLink;
use ForkCMS\Modules\Backend\Domain\Action\ModuleAction;
use ForkCMS\Modules\Backend\Domain\User\User;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleName;
use ForkCMS\Modules\Frontend\Domain\Meta\Meta;
use ForkCMS\Modules\Frontend\Domain\Privacy\ConsentDialog;
use ForkCMS\Modules\Internationalisation\Domain\Translator\ForkTranslator;
use InvalidArgumentException;
use LogicException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * This class will be used to alter the head-part of the HTML-document that will be created by he Backend
 * Therefore it will handle meta-stuff (title, including JS, including CSS, ...).
 */
final readonly class Header
{
    public JsData $jsData;

    public AssetCollection $cssFiles;
    public AssetCollection $jsFiles;

    public function __construct(
        public BreadcrumbCollection $breadcrumbs,
        public PageTitle $pageTitle,
        public ContentTitle $contentTitle,
        public MetaCollection $meta,
        private RequestStack $requestStack,
        KernelInterface $kernel,
        Security $security,
        TranslatorInterface $translator,
        ConsentDialog $consentDialog,
    ) {
        $this->jsData = $this->initJsData($kernel, $security, $translator, $consentDialog);
        $this->jsFiles = new AssetCollection();
        $this->cssFiles = new AssetCollection();
    }

    public function appendMeta(Meta $meta): void
    {
        $this->contentTitle->overwriteContentTitle($meta->title);
        $this->meta->addDescription($meta->description, $meta->descriptionOverwrite);
        $this->meta->addKeywords($meta->keywords, $meta->keywordsOverwrite);
        $this->meta->setSEOFollow($meta->seoFollow);
        $this->meta->setSEOIndex($meta->seoIndex);

        if ($meta->canonicalUrlOverwrite && $meta->canonicalUrl !== null && $meta->canonicalUrl !== '') {
            $this->meta->addMetaLink(MetaLink::canonical($meta->canonicalUrl));
        }
    }

    private function initJsData(
        KernelInterface $kernel,
        Security $security,
        TranslatorInterface $translator,
        ConsentDialog $consentDialog
    ): JsData {
        $defaults = [
            'default_locale' => $kernel->getContainer()->getParameter('kernel.default_locale'),
            'debug' => $kernel->isDebug(),
            'session_timeout' => $this->getFirstPossibleSessionTimeout(),
            'privacyConsent' => $consentDialog,
        ];
        $defaults['locale'] = $translator->getLocale();
        $user = $security->getUser();
        if ($user instanceof User) {
            $defaults['locale'] = $user->getSetting('locale', $defaults['locale']);
        }
        if ($translator instanceof ForkTranslator) {
            $translationDomain = $translator->getDefaultTranslationDomain();
            $defaults['default_translation_domain'] = $translationDomain->getDomain();
            $fallbackDomain = $translationDomain->getFallback()?->getDomain();
            $defaultTranslationDomain = $fallbackDomain ?? $defaults['default_translation_domain'];
            $defaults['default_translation_domain_fallback'] = $defaultTranslationDomain;
        }

        return new JsData($defaults);
    }

    public function addJsData(ModuleName $module, string $key, mixed $value): void
    {
        $this->jsData->add($module, $key, $value);
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
                $flashMessage->type->value,
                $flashMessage->message
            );
        } catch (SessionNotFoundException $e) {
            throw new LogicException(
                'You cannot use the addFlash method if sessions are disabled. ' .
                'Enable them in "config/packages/framework.yaml".',
                0,
                $e
            );
        }
    }

    public function addJs(Asset $assets): void
    {
        $this->jsFiles->add($assets);
    }

    public function addCss(Asset $assets): void
    {
        $this->cssFiles->add($assets);
    }

    public function addBreadcrumb(Breadcrumb $breadcrumb): void
    {
        $this->breadcrumbs->add($breadcrumb);
    }

    public function addAssetsForAction(ModuleAction $moduleAction): void
    {
        $module = $moduleAction->module;
        try {
            $this->addJs(
                Asset::forModule(
                    Application::BACKEND,
                    $module,
                    'js/' . $module . '.js',
                    priority: Priority::forModuleName($module)
                )
            );
        } catch (InvalidArgumentException) {
            // No module js file found
        }

        try {
            $this->addJs(
                Asset::forModule(
                    Application::BACKEND,
                    $module,
                    'js/' . $moduleAction->action->name . '.js',
                    priority: Priority::forModuleName($module)
                )
            );
        } catch (InvalidArgumentException) {
            // No module action js file found
        }

        try {
            $this->addCss(
                Asset::forModule(
                    Application::BACKEND,
                    $module,
                    'css/' . $module . '.css',
                    priority: Priority::forModuleName($module)
                )
            );
        } catch (InvalidArgumentException) {
            // No module css file found
        }

        try {
            $this->addCss(
                Asset::forModule(
                    Application::BACKEND,
                    $module,
                    'css/' . $moduleAction->action->name . '.css',
                    priority: Priority::forModuleName($module)
                )
            );
        } catch (InvalidArgumentException) {
            // No module action css file found
        }
    }
}
