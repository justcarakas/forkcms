<?php

declare(strict_types=1);

namespace ForkCMS\Core\Domain\Header;

use ForkCMS\Core\Domain\Application\Application;
use ForkCMS\Core\Domain\Header\Asset\DropInStimulusControllerFinder;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleName;
use Symfony\Component\HttpFoundation\RequestStack;

final class JsData
{
    /** @param mixed[] $jsData */
    public function __construct(
        private array $jsData = [],
        private ?DropInStimulusControllerFinder $dropInStimulusControllerFinder = null,
        private ?RequestStack $requestStack = null,
    ) {
    }

    public function add(ModuleName $module, string $key, mixed $value): void
    {
        $this->jsData[$module->name][$key] = $value;
    }

    public function __toString(): string
    {
        $jsData = $this->jsData;
        $application = $this->resolveApplication();

        if ($application !== null && $this->dropInStimulusControllerFinder !== null) {
            $jsData[ModuleName::core()->name]['dropInStimulusControllers']
                = $this->dropInStimulusControllerFinder->find($application);
        }

        return '<script>var jsData = ' . json_encode($jsData, JSON_THROW_ON_ERROR) . '</script>';
    }

    /**
     * Resolved lazily (only once this is actually rendered, i.e. after routing has run) rather than
     * in a constructor - JsData is built while assembling the response, before routing attributes
     * like _route/_locale_application exist yet, so resolving this any earlier always misses.
     */
    private function resolveApplication(): ?Application
    {
        $request = $this->requestStack?->getMainRequest();
        if ($request === null) {
            return null;
        }

        if ($request->attributes->has('_locale_application')) {
            $application = Application::tryFrom((string) $request->attributes->get('_locale_application'));
            if ($application instanceof Application) {
                return $application;
            }
        }

        return match ($request->attributes->get('_route')) {
            'backend_action', 'backend_login', 'backend_ajax' => Application::BACKEND,
            default => Application::FRONTEND,
        };
    }
}
