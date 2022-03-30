<?php

namespace ForkCMS\Modules\Extensions\Domain\Module;

use ForkCMS\Modules\Extensions\Domain\InformationFile\Author;
use ForkCMS\Modules\Extensions\Domain\InformationFile\Messages;
use ForkCMS\Modules\Extensions\Domain\InformationFile\Requirements;
use ForkCMS\Modules\Extensions\Domain\InformationFile\SafeHtml;
use ForkCMS\Modules\Extensions\Domain\InformationFile\SafeString;
use ForkCMS\Modules\Internationalisation\Domain\Translation\TranslationKey;

final class ModuleInformation
{
    private function __construct(
        public readonly ModuleName $name,
        public readonly string $version,
        public readonly ?string $description,
        public readonly array $authors,
        public readonly array $events,
        public readonly Messages $messages,
    ) {
    }

    public static function fromModule(ModuleName $moduleName): self
    {
        $path = realpath(__DIR__ . '/../../../' . $moduleName . '/module.xml');

        if ($path === false) {
            return new self(
                $moduleName,
                '1.0.0',
                $moduleName . ' does not have a module.xml file with more information.',
                [],
                [],
                []
            );
        }
        return self::fromXML($path);
    }

    public static function fromXML(string $xmlFilePath): self
    {
        $moduleConfig = simplexml_load_string(file_get_contents($xmlFilePath), 'SimpleXMLElement', LIBXML_NOCDATA);
        $messages = new Messages();
        Requirements::fromXML($moduleConfig->requirements, $messages);
        $name = ModuleName::fromString(SafeString::fromXML($moduleConfig->name));
        $directoryName = basename(dirname($xmlFilePath));
        if ($name->getName() !== $directoryName) {
            $messages->addMessage(TranslationKey::error('ModuleNameDoesntMatch'));
        }

        $moduleVersion = SafeString::fromXML($moduleConfig->version)->string;
        if ($moduleVersion === '') {
            $moduleVersion = '1.0.0';
        }

        $authors = [];
        foreach ($moduleConfig->authors->author as $authorConfig) {
            $authors[] = Author::fromXML($authorConfig);
        }

        $events = [];
        foreach ($moduleConfig->events->event as $eventConfig) {
            $class = SafeString::fromXML($eventConfig->attributes()->class);
            if (class_exists($class)) {
                $events[] = [
                    'class' => SafeString::fromXML($eventConfig->attributes()->class)->string,
                    'description' => SafeHtml::fromXML($eventConfig)->html,
                ];
            }
        }

        return new self(
            $name,
            $moduleVersion,
            SafeHtml::fromXML($moduleConfig->description),
            $authors,
            $events,
            $messages
        );
    }

    public function isInstallable(): bool
    {
        return !$this->messages->hasErrors();
    }
}
