<?php

namespace ForkCMS\Modules\Internationalisation\Domain\ModuleSettings\Command;

use ForkCMS\Modules\Internationalisation\Domain\Locale\InstalledLocale;
use ForkCMS\Modules\Internationalisation\Domain\Locale\InstalledLocaleDataTransferObject;
use ForkCMS\Modules\Internationalisation\Domain\Locale\Locale;

final class ChangeModuleSettings
{
    public Locale $defaultForWebsite;
    public Locale $defaultForUser;

    /** @var InstalledLocaleDataTransferObject[] */
    public array $installedLocales = [];

    public function __construct(InstalledLocale ...$installedLocales)
    {
        foreach ($installedLocales as $installedLocale) {
            if ($installedLocale->isDefaultForWebsite()) {
                $this->defaultForWebsite = $installedLocale->getLocale();
            }
            if ($installedLocale->isDefaultForUser()) {
                $this->defaultForUser = $installedLocale->getLocale();
            }
            $tabKey = md5($installedLocale->getLocale()->asTranslatable());
            $this->installedLocales[$tabKey] = new InstalledLocaleDataTransferObject($installedLocale);
        }
    }
}
