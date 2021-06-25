<?php

namespace ForkCMS\Modules\Internationalisation\Domain\Importer;

use ForkCMS\Core\Domain\Application\Application;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleName;
use ForkCMS\Modules\Internationalisation\Domain\Locale\Locale;
use ForkCMS\Modules\Internationalisation\Domain\Translation\Translation;
use ForkCMS\Modules\Internationalisation\Domain\Translation\TranslationDomain;
use ForkCMS\Modules\Internationalisation\Domain\Translation\TranslationKey;
use ForkCMS\Modules\Internationalisation\Domain\Translation\Type;
use Generator;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Encoder\XmlEncoder;

final class XmlImporter implements ImporterInterface
{
    public function getTranslations(File $translationFile): Generator
    {
        $xmlDecoder = new XmlEncoder;
        $xmlData = $xmlDecoder->decode($translationFile->getContent(), 'xml');
        foreach ($xmlData as $application => $modules) {
            $application = Application::from($application);
            foreach ($modules as $module => $translationItems) {
                $domain = new TranslationDomain($application, ModuleName::fromString($module));
                foreach ($translationItems as $translationItem) {
                    $key = TranslationKey::forType(Type::from($translationItem['@type']), $translationItem['@name']);
                    foreach ($translationItem['translation'] as $translation) {
                        yield new Translation($domain, $key, Locale::from($translation['@locale']), $translation['#']);
                    }
                }
            }
        }
    }

    public static function forExtension(): string
    {
        return 'xml';
    }
}
