<?php

declare(strict_types=1);

namespace ForkCMS\Modules\Internationalisation\Domain\Exporter;

use DOMDocument;
use ForkCMS\Modules\Internationalisation\Domain\Translation\Translation;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(index: 'xml')]
final class XmlExporter implements ExporterInterface
{
    /** @param iterable<Translation> $translations */
    public function exportTranslations(iterable $translations): string
    {
        $xml = new DOMDocument('1.0', 'utf-8');

        $xml->preserveWhiteSpace = false;
        $xml->formatOutput = true;

        $root = $xml->createElement('translations');
        $xml->appendChild($root);

        $currentApplication = null;
        $currentModule = null;
        $currentTranslationKey = null;
        $applicationElement = null;
        $moduleElement = null;
        $translationItemElement = null;

        /** @var Translation $translation */
        foreach ($translations as $translation) {
            if ($currentApplication !== $translation->domain->application) {
                $currentApplication = $translation->domain->application;
                $applicationElement = $xml->createElement($currentApplication->value);
                $root->appendChild($applicationElement);
                $translationItemElement = null;
            }
            if ($currentModule->name !== $translation->domain->moduleName->name) {
                $currentModule = $translation->domain->moduleName;
                if ($currentModule !== null) {
                    $moduleElement = $xml->createElement($currentModule->name);
                    $applicationElement->appendChild($moduleElement);
                } else {
                    $moduleElement = null;
                }
                $translationItemElement = null;
            }

            if ($translationItemElement === null || !$translation->key->equals($currentTranslationKey)) {
                $translationItemElement = $xml->createElement('item');
                if ($moduleElement !== null) {
                    $moduleElement->appendChild($translationItemElement);
                } else {
                    $applicationElement->appendChild($translationItemElement);
                }
                $translationItemElement->setAttribute('type', $translation->key->type->value);
                $translationItemElement->setAttribute('name', (string) $translation->key);

                $currentTranslationKey = $translation->key;
            }

            $translationElement = $xml->createElement('translation');
            $translationElement->setAttribute('locale', $translation->locale->value);
            if ($translation->source !== null) {
                $translationElement->setAttribute('source', $translation->source);
            }
            $translationElement->nodeValue = sprintf('<![CDATA[%1$s]]>', $translation->value);
            $translationItemElement->appendChild($translationElement);
        }

        return $xml->saveXML();
    }
}
