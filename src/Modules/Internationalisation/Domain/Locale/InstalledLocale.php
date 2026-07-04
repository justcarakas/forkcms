<?php

declare(strict_types=1);

namespace ForkCMS\Modules\Internationalisation\Domain\Locale;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ForkCMS\Core\Domain\Settings\EntityWithSettingsTrait;
use ForkCMS\Core\Domain\Settings\SettingsBag;
use ForkCMS\Core\Domain\Util\Ensure;
use ForkCMS\Modules\Backend\Domain\User\Blameable\CreatedAndUpdatedBy;

#[ORM\Entity(repositoryClass: InstalledLocaleRepository::class)]
#[ORM\Index(name: 'idx_default_for_website', columns: ['isDefaultForWebsite'])]
#[ORM\Index(name: 'idx_default_for_user', columns: ['isDefaultForUser'])]
class InstalledLocale
{
    use EntityWithSettingsTrait;
    use CreatedAndUpdatedBy;

    #[ORM\Id]
    #[ORM\Column(type: Types::STRING, length: 5, enumType: Locale::class)]
    private(set) Locale $locale;

    #[ORM\Column(type: Types::BOOLEAN)]
    private(set) bool $isEnabledForWebsite;

    #[ORM\Column(type: Types::BOOLEAN)]
    private(set) bool $isDefaultForWebsite;

    #[ORM\Column(type: Types::BOOLEAN)]
    private(set) bool $isEnabledForBrowserLocaleRedirect;

    #[ORM\Column(type: Types::BOOLEAN)]
    private(set) bool $isEnabledForUser;

    #[ORM\Column(type: Types::BOOLEAN)]
    private(set) bool $isDefaultForUser;

    private function __construct(Locale $locale)
    {
        $this->locale = $locale;
    }

    public static function fromDataTransferObject(InstalledLocaleDataTransferObject $locale): self
    {
        $installedLocale = $locale->hasEntity() ? $locale->getEntity() : new self(Ensure::isNotNull($locale->locale));

        $installedLocale->isEnabledForWebsite = $locale->isEnabledForWebsite;
        $installedLocale->isDefaultForWebsite = $locale->isDefaultForWebsite;
        $installedLocale->isEnabledForBrowserLocaleRedirect = $locale->isEnabledForBrowserLocaleRedirect;
        $installedLocale->isEnabledForUser = $locale->isEnabledForUser;
        $installedLocale->isDefaultForUser = $locale->isDefaultForUser;
        $installedLocale->settings = new SettingsBag($locale->settings);

        return $installedLocale;
    }
}
