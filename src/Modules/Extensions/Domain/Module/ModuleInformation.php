<?php

declare(strict_types=1);

namespace ForkCMS\Modules\Extensions\Domain\Module;

use ForkCMS\Core\Domain\Util\Ensure;
use ForkCMS\Modules\Backend\Domain\Action\ModuleAction;
use ForkCMS\Modules\Extensions\Domain\InformationFile\Author;
use ForkCMS\Modules\Extensions\Domain\InformationFile\Messages;
use ForkCMS\Modules\Extensions\Domain\InformationFile\Requirements;
use ForkCMS\Modules\Extensions\Domain\InformationFile\SafeHtml;
use ForkCMS\Modules\Extensions\Domain\InformationFile\SafeString;
use ForkCMS\Modules\Internationalisation\Domain\Translation\TranslationKey;
use Pageon\DoctrineDataGridBundle\Attribute\DataGrid;
use Pageon\DoctrineDataGridBundle\Attribute\DataGridActionColumn;
use Pageon\DoctrineDataGridBundle\Attribute\DataGridPropertyColumn;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

#[DataGrid('moduleInformation')]
#[DataGridActionColumn(
    route: 'backend_action',
    routeAttributes: ['module' => 'extensions', 'action' => 'module-detail'],
    routeAttributesCallback: [self::class, 'dataGridSlugCallback'],
    label: 'lbl.Details',
    class: 'btn btn-default btn-sm',
    iconClass: 'fa fa-eye',
    requiredRole: ModuleAction::ROLE_PREFIX . 'EXTENSIONS__MODULE_DETAIL',
    columnAttributes: ['class' => 'fork-data-grid-action'],
)]
final readonly class ModuleInformation
{
    private function __construct(
        #[DataGridPropertyColumn(
            label: 'lbl.Name',
            route: 'backend_action',
            routeAttributes: ['module' => 'extensions', 'action' => 'module-detail'],
            routeAttributesCallback: [self::class, 'dataGridSlugCallback'],
            routeRole: ModuleAction::ROLE_PREFIX . 'EXTENSIONS__MODULE_DETAIL',
            columnAttributes: ['class' => 'title'],
        )]
        public ModuleName $name,
        #[DataGridPropertyColumn(label: 'lbl.Version')]
        public string $version,
        #[DataGridPropertyColumn(label: 'lbl.Description', valueCallback: [self::class, 'truncateDescription'])]
        public ?string $description,
        /** @var Author[] $authors */
        public array $authors,
        /** @var array<int, array<string, string>> $events */
        public array $events,
        public Messages $messages,
    ) {
    }

    public static function fromModule(ModuleName $moduleName): self
    {
        $moduleDirectory = __DIR__ . '/../../../' . $moduleName;

        if (is_dir($moduleDirectory) === false) {
            throw new NotFoundHttpException('The module directory does not exist');
        }

        $path = realpath($moduleDirectory . '/module.xml');

        if ($path === false) {
            return new self(
                $moduleName,
                '1.0.0',
                $moduleName . ' does not have a module.xml file with more information.',
                [],
                [],
                new Messages(),
            );
        }

        return self::fromXML($path);
    }

    public static function fromXML(string $xmlFilePath): self
    {
        $invalidXML = new self(
            ModuleName::fromString(basename(dirname($xmlFilePath))),
            '?.?.?',
            '',
            [],
            [],
            new Messages([TranslationKey::error('InvalidXML')])
        );

        try {
            $xmlContents = file_get_contents($xmlFilePath);
            if ($xmlContents === false) {
                return $invalidXML;
            }

            $moduleConfig = simplexml_load_string(
                $xmlContents,
                'SimpleXMLElement',
                LIBXML_NOCDATA | LIBXML_NOERROR | LIBXML_NOWARNING
            );
            if ($moduleConfig === false) {
                return $invalidXML;
            }
        } catch (Throwable) {
            return $invalidXML;
        }
        $messages = new Messages();
        Requirements::fromXML($moduleConfig->requirements, $messages);
        $name = ModuleName::fromString(SafeString::fromXML($moduleConfig->name)->string);
        $directoryName = basename(dirname($xmlFilePath));
        if ($name->name !== $directoryName) {
            $messages->addMessage(TranslationKey::error('ModuleNameDoesntMatch'));
        }

        $moduleVersion = SafeString::fromXML($moduleConfig->version)->string;
        if ($moduleVersion === '') {
            $moduleVersion = '1.0.0';
        }

        $authors = [];
        foreach ($moduleConfig->authors->author as $authorConfig) {
            $authors[] = Author::fromXML(Ensure::isNotNull($authorConfig));
        }

        $events = [];
        if ($moduleConfig->events->event !== null) {
            foreach ($moduleConfig->events->event as $eventConfig) {
                $eventConfig = Ensure::isNotNull($eventConfig);
                $class = SafeString::fromXML($eventConfig->attributes()->class);
                if (class_exists((string) $class)) {
                    $events[] = [
                        'class' => SafeString::fromXML($eventConfig->attributes()->class)->string,
                        'description' => SafeHtml::fromXML($eventConfig)->html,
                    ];
                }
            }
        }

        return new self(
            $name,
            $moduleVersion,
            SafeHtml::fromXML($moduleConfig->description)->html,
            $authors,
            $events,
            $messages
        );
    }

    public function isInstallable(): bool
    {
        return !$this->messages->hasErrors();
    }

    public static function truncateDescription(string $description): string
    {
        $description = strip_tags($description);
        if (strlen($description) > 100) {
            return substr($description, 0, 100) . '...';
        }

        return $description;
    }

    /**
     * @param array{string?: string} $attributes
     *
     * @return array{string?: string}
     */
    public static function dataGridSlugCallback(self $moduleInformation, array $attributes): array
    {
        $attributes['slug'] = $moduleInformation->name;

        return $attributes;
    }
}
