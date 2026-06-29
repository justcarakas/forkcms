<?php

namespace ForkCMS\Core\tests\DependencyInjection;

use Doctrine\DBAL\Types\Types;
use ForkCMS\Core\Domain\Doctrine\UTCDateTimeImmutableDBALType;
use ForkCMS\Core\Domain\Doctrine\UTCDateTimeMutableDBALType;
use ForkCMS\Core\Domain\Doctrine\UTCTimeImmutableDBALType;
use ForkCMS\Core\Domain\Doctrine\UTCTimeMutableDBALType;
use ForkCMS\Core\Domain\Doctrine\ValueObjectDBALType;
use ForkCMS\Core\Domain\Settings\SettingsBagDBALType;
use ForkCMS\Core\DependencyInjection\CoreExtension;
use ForkCMS\Modules\Backend\DependencyInjection\BackendExtension;
use ForkCMS\Modules\Backend\Domain\Action\ActionNameDBALType;
use ForkCMS\Modules\Backend\Domain\Action\ActionSlugDBALType;
use ForkCMS\Modules\Backend\Domain\AjaxAction\AjaxActionNameDBALType;
use ForkCMS\Modules\Backend\Domain\AjaxAction\AjaxActionSlugDBALType;
use ForkCMS\Modules\Backend\Domain\Widget\WidgetNameDBALType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class ForkModuleExtensionTest extends TestCase
{
    public function testValueObjectTypesAreRegisteredByFqcn(): void
    {
        $types = $this->getPrependedTypes(new BackendExtension());

        self::assertArrayHasKey(ActionNameDBALType::class, $types);
        self::assertArrayHasKey(ActionSlugDBALType::class, $types);
        self::assertArrayHasKey(AjaxActionNameDBALType::class, $types);
        self::assertArrayHasKey(AjaxActionSlugDBALType::class, $types);
        self::assertArrayHasKey(WidgetNameDBALType::class, $types);
    }

    public function testUtcTypesAreRegisteredUnderDoctrineTypeNames(): void
    {
        $types = $this->getPrependedTypes(new CoreExtension());

        self::assertSame(UTCDateTimeImmutableDBALType::class, $types[Types::DATETIME_IMMUTABLE]);
        self::assertSame(UTCDateTimeMutableDBALType::class, $types[Types::DATETIME_MUTABLE]);
        self::assertSame(UTCTimeImmutableDBALType::class, $types[Types::TIME_IMMUTABLE]);
        self::assertSame(UTCTimeMutableDBALType::class, $types[Types::TIME_MUTABLE]);
    }

    public function testSettingsBagTypeIsRegistered(): void
    {
        $types = $this->getPrependedTypes(new CoreExtension());

        self::assertSame(SettingsBagDBALType::class, $types[SettingsBagDBALType::class]);
    }

    public function testAbstractTypesAreSkipped(): void
    {
        $types = $this->getPrependedTypes(new CoreExtension());

        self::assertArrayNotHasKey(ValueObjectDBALType::class, $types);
    }

    /** @return array<string, string> */
    private function getPrependedTypes(object $extension): array
    {
        $container = new ContainerBuilder();
        $extension->prepend($container);

        $types = [];
        foreach ($container->getExtensionConfig('doctrine') as $config) {
            $types = array_merge($types, $config['dbal']['types'] ?? []);
        }

        return $types;
    }
}