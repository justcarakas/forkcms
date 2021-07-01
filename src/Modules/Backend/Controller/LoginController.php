<?php

namespace ForkCMS\Modules\Backend\Controller;

use ForkCMS\Modules\Backend\Backend\Actions\AuthenticationLogin;
use ForkCMS\Modules\Backend\Domain\User\User;
use ForkCMS\Modules\Internationalisation\Domain\Translation\TranslationKey;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class LoginController
{
    public function __construct(
        private Environment $twig,
        private AuthenticationUtils $authenticationUtils,
        private TranslatorInterface $translator,
        private Security $security,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $currentUser = $this->security->getUser();
        if ($currentUser instanceof User) {
            return new RedirectResponse(AuthenticationLogin::getActionSlug()->generateRoute($this->urlGenerator));
        }

        // get the login error if there is one
        $error = $this->authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $this->authenticationUtils->getLastUsername();

        return new Response(
            $this->twig->render(
                '@Backend/Backend/login.html.twig',
                [
                    'last_username' => $lastUsername,
                    'error' => $error,
                    'INTERFACE_LANGUAGE' => $request->getLocale(),
                    'page_title' => $this->translator->trans(TranslationKey::label('Login')),
                    'SITE_TITLE' => $_ENV['SITE_DEFAULT_TITLE'],
                    'SITE_URL' => $_ENV['SITE_PROTOCOL'] . '://' . $_ENV['SITE_DOMAIN'],
                    'jsFiles' => [],
                    'jsData' => null,
                ]
            )
        );
    }
}
