<?php

namespace ForkCMS\Modules\Internationalisation\Domain\ModuleSettings\Command;

use ForkCMS\Core\Domain\MessageHandler\CommandHandlerInterface;
use ForkCMS\Modules\Internationalisation\Domain\Locale\InstalledLocale;
use ForkCMS\Modules\Internationalisation\Domain\Locale\InstalledLocaleRepository;

final class ChangeModuleSettingsHandler implements CommandHandlerInterface
{
    public function __construct(private readonly InstalledLocaleRepository $installedLocaleRepository)
    {
    }

    public function __invoke(ChangeModuleSettings $changeSettings): void
    {
        foreach ($changeSettings->installedLocales as $locale) {
            $locale->isDefaultForUser = $locale->locale === $changeSettings->defaultForUser;
            $locale->isDefaultForWebsite = $locale->locale === $changeSettings->defaultForWebsite;

            $this->installedLocaleRepository->save(InstalledLocale::fromDataTransferObject($locale));
        }
    }
}
