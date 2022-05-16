<?php

namespace ForkCMS\Modules\Frontend\Domain\Privacy;

use ForkCMS\Modules\Extensions\Domain\Module\ModuleName;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleSettings;
use JsonSerializable;
use Symfony\Component\HttpFoundation\RequestStack;

class ConsentDialog implements JsonSerializable
{
    public function __construct(private readonly ModuleSettings $settings, private readonly RequestStack $requestStack)
    {
    }

    public function isDialogEnabled(): bool
    {
        return $this->settings->get(ModuleName::fromString('Frontend'), 'show_consent_dialog', false);
    }

    public function shouldDialogBeShown(): bool
    {
        // the cookiebar is hidden within the settings, so don't show it
        if (!$this->settings->get(ModuleName::fromString('Frontend'), 'show_consent_dialog', false)) {
            return false;
        }

        // no levels mean there should not be any consent
        if (empty($this->getLevels())) {
            return false;
        }

        // if the hash in the cookie is the same as the current has it means the user
        // has already stored their preferences
        if ($this->requestStack->getCurrentRequest()->cookies->get('privacy_consent_hash', '') === $this->getLevelsHash()) {
            return false;
        }

        return true;
    }

    public function getLevels(bool $includeFunctional = false): array
    {
        $levels = [];
        if ($includeFunctional) {
            $levels = ['functional'];
        }

        $customLevels = $this->settings->get(ModuleName::fromString('Frontend'), 'privacy_consent_levels', []);

        return array_filter(array_merge($levels, $customLevels));
    }

    public function getLevelsHash(): string
    {
        $levels = $this->getLevels(true);
        sort($levels);

        return md5(implode('|', $levels));
    }

    public function getVisitorChoices(): array
    {
        $choices = [
            'functional' => true,
        ];
        $levels = $this->getLevels();
        foreach ($levels as $level) {
            $enabled = $this->requestStack->getCurrentRequest()->cookies->get(
                'privacy_consent_level_' . $level . '_agreed',
                '0'
            );
            $choices[$level] = $enabled === '1';
        }

        return $choices;
    }

    public function hasAgreedTo(string $level): bool
    {
        $choices = $this->getVisitorChoices();
        if (!array_key_exists($level, $choices)) {
            return false;
        }

        return $choices[$level];
    }

    public function jsonSerialize(): array
    {
        return [
            'possibleLevels' => $this->getLevels(true),
            'levelsHash' => $this->getLevelsHash(),
            'visitorChoices' => $this->getVisitorChoices(),
        ];
    }
}
