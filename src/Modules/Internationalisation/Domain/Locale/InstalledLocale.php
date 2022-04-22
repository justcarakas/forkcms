<?php

namespace ForkCMS\Modules\Internationalisation\Domain\Locale;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ForkCMS\Core\Domain\Settings\EntityWithSettingsTrait;
use ForkCMS\Core\Domain\Settings\SettingsBag;
use ForkCMS\Modules\Backend\Domain\User\Blameable;

#[ORM\Entity(repositoryClass: InstalledLocaleRepository::class)]
#[ORM\Table(name: 'internationalisation__installed_locale')]
#[ORM\Index(columns: ['isDefaultForWebsite'], name: 'default_for_website')]
#[ORM\Index(columns: ['isDefaultForUser'], name: 'default_for_user')]
class InstalledLocale
{
    use EntityWithSettingsTrait;
    use Blameable;

    #[ORM\Id]
    #[ORM\Column(type: Types::STRING, length: 5, enumType: Locale::class)]
    private Locale $locale;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $isEnabledForWebsite;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $isDefaultForWebsite;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $isEnabledForBrowserLocaleRedirect;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $isEnabledForUser;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $isDefaultForUser;

    public function __construct(
        Locale $locale,
        bool $isEnabledForWebsite = true,
        bool $isDefaultForWebsite = false,
        bool $isEnabledForBrowserLocaleRedirect = true,
        bool $isEnabledForUser = true,
        bool $isDefaultForUser = false,
        SettingsBag $settings = new SettingsBag(),
    ) {
        $this->locale = $locale;
        $this->isEnabledForWebsite = $isEnabledForWebsite;
        $this->isDefaultForWebsite = $isDefaultForWebsite;
        $this->isEnabledForBrowserLocaleRedirect = $isEnabledForBrowserLocaleRedirect;
        $this->isEnabledForUser = $isEnabledForUser;
        $this->isDefaultForUser = $isDefaultForUser;
        $this->settings = $settings;
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
