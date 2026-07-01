<?php

declare(strict_types=1);

namespace ForkCMS\Core\tests\Domain\Doctrine;

use Doctrine\DBAL\Types\Types;
use ForkCMS\Core\Domain\Doctrine\UTCDateTimeImmutableDBALType;
use ForkCMS\Core\Domain\Doctrine\UTCDateTimeMutableDBALType;
use ForkCMS\Core\Domain\Doctrine\UTCTimeImmutableDBALType;
use ForkCMS\Core\Domain\Doctrine\UTCTimeMutableDBALType;
use ForkCMS\Core\Domain\Settings\SettingsBagDBALType;
use PHPUnit\Framework\TestCase;

final class DBALTypeNamesTest extends TestCase
{
    public function testTraitGetNameReturnsFqcn(): void
    {
        self::assertSame(SettingsBagDBALType::class, SettingsBagDBALType::getName());
    }

    public function testDateTimeImmutableGetNameReturnsDoctrineConstant(): void
    {
        self::assertSame(Types::DATETIME_IMMUTABLE, UTCDateTimeImmutableDBALType::getName());
    }

    public function testDateTimeMutableGetNameReturnsDoctrineConstant(): void
    {
        self::assertSame(Types::DATETIME_MUTABLE, UTCDateTimeMutableDBALType::getName());
    }

    public function testTimeImmutableGetNameReturnsDoctrineConstant(): void
    {
        self::assertSame(Types::TIME_IMMUTABLE, UTCTimeImmutableDBALType::getName());
    }

    public function testTimeMutableGetNameReturnsDoctrineConstant(): void
    {
        self::assertSame(Types::TIME_MUTABLE, UTCTimeMutableDBALType::getName());
    }
}
