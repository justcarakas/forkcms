<?php

namespace ForkCMS\Modules\Internationalisation\Domain\Locale;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="ForkCMS\Modules\Internationalisation\Domain\Locale\InstalledLocaleRepository")
 * @ORM\Table(
 *     name="locales",
 *     indexes={
 *         @ORM\Index(name="default_for_website", columns={"isDefaultForWebsite"}),
 *         @ORM\Index(name="default_for_user", columns={"isDefaultForUser"})
 *     }
 * )
 * @ORM\HasLifecycleCallbacks
 */
final class InstalledLocale
{
    /**
     * @ORM\Id
     * @ORM\Column(type="modules__internationalisation__locale__locale")
     */
    private Locale $locale;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $isEnabledForWebsite;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $isDefaultForWebsite;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $isEnabledForBrowserLocaleRedirect;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $isEnabledForUser;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $isDefaultForUser;

    public function __construct(
        Locale $locale,
        bool $isEnabledForWebsite = true,
        bool $isDefaultForWebsite = false,
        bool $isEnabledForBrowserLocaleRedirect = true,
        bool $isEnabledForUser = true,
        bool $isDefaultForUser = false,
    ) {
        $this->locale = $locale;
        $this->isEnabledForWebsite = $isEnabledForWebsite;
        $this->isDefaultForWebsite = $isDefaultForWebsite;
        $this->isEnabledForBrowserLocaleRedirect = $isEnabledForBrowserLocaleRedirect;
        $this->isEnabledForUser = $isEnabledForUser;
        $this->isDefaultForUser = $isDefaultForUser;
    }

    public function getLocale(): Locale
    {
        return $this->locale;
    }

    public function isEnabledForWebsite(): bool
    {
        return $this->isEnabledForWebsite;
    }

    public function isDefaultForWebsite(): bool
    {
        return $this->isDefaultForWebsite;
    }

    public function isEnabledForBrowserLocaleRedirect(): bool
    {
        return $this->isEnabledForBrowserLocaleRedirect;
    }

    public function isEnabledForUser(): bool
    {
        return $this->isEnabledForUser;
    }

    public function isDefaultForUser(): bool
    {
        return $this->isDefaultForUser;
    }
}
