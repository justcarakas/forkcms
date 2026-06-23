<?php

namespace ForkCMS\Modules\Extensions\tests\Domain\Module\Command;

use ForkCMS\Modules\Extensions\Domain\Module\Command\ChangeModuleSettings;
use ForkCMS\Modules\Extensions\Domain\Module\Module;
use PHPUnit\Framework\TestCase;

class ChangeModuleSettingsTest extends TestCase
{
    private Module $core;
    private Module $module;

    protected function setUp(): void
    {
        $this->core = Module::fromString('Core');
        $this->module = Module::fromString('Blog');
    }

    public function testGetReturnsModuleSetting(): void
    {
        $this->module->getSettings()->set('foo', 'bar');
        $command = new ChangeModuleSettings($this->core, $this->module, []);

        self::assertSame('bar', $command->foo);
    }

    public function testGetFallsBackToDefault(): void
    {
        $command = new ChangeModuleSettings($this->core, $this->module, ['foo' => 'default']);

        self::assertSame('default', $command->foo);
    }

    public function testGetReturnsNullWhenNotFoundAndNoDefault(): void
    {
        $command = new ChangeModuleSettings($this->core, $this->module, []);

        self::assertNull($command->missing);
    }

    public function testGetPrefersStoredSettingOverDefault(): void
    {
        $this->module->getSettings()->set('foo', 'stored');
        $command = new ChangeModuleSettings($this->core, $this->module, ['foo' => 'default']);

        self::assertSame('stored', $command->foo);
    }

    public function testGetCoreSettingWithPrefix(): void
    {
        $this->core->getSettings()->set('site_title', 'Fork CMS');
        $command = new ChangeModuleSettings($this->core, $this->module, []);

        self::assertSame('Fork CMS', $command->{'core:site_title'});
    }

    public function testGetCoreFallsBackToCoreDefault(): void
    {
        $command = new ChangeModuleSettings($this->core, $this->module, ['core' => ['site_title' => 'Default Title']]);

        self::assertSame('Default Title', $command->{'core:site_title'});
    }

    public function testGetCoreReturnsNullWhenAbsent(): void
    {
        $command = new ChangeModuleSettings($this->core, $this->module, []);

        self::assertNull($command->{'core:missing'});
    }

    public function testSetWritesToModuleSettings(): void
    {
        $command = new ChangeModuleSettings($this->core, $this->module, []);
        $command->foo = 'written';

        self::assertSame('written', $this->module->getSettings()->getOr('foo'));
    }

    public function testSetWithCorePrefixWritesToCoreSettings(): void
    {
        $command = new ChangeModuleSettings($this->core, $this->module, []);
        $command->{'core:site_title'} = 'Updated';

        self::assertSame('Updated', $this->core->getSettings()->getOr('site_title'));
        self::assertFalse($this->module->getSettings()->has('site_title'));
    }

    public function testIssetReturnsTrueForStoredModuleSetting(): void
    {
        $this->module->getSettings()->set('foo', 'bar');
        $command = new ChangeModuleSettings($this->core, $this->module, []);

        self::assertTrue(isset($command->foo));
    }

    public function testIssetReturnsTrueForDefault(): void
    {
        $command = new ChangeModuleSettings($this->core, $this->module, ['foo' => 'default']);

        self::assertTrue(isset($command->foo));
    }

    public function testIssetReturnsFalseWhenAbsent(): void
    {
        $command = new ChangeModuleSettings($this->core, $this->module, []);

        self::assertFalse(isset($command->missing));
    }

    public function testIssetReturnsTrueForStoredCoreSetting(): void
    {
        $this->core->getSettings()->set('site_title', 'Fork CMS');
        $command = new ChangeModuleSettings($this->core, $this->module, []);

        self::assertTrue(isset($command->{'core:site_title'}));
    }

    public function testIssetReturnsFalseForAbsentCoreSetting(): void
    {
        $command = new ChangeModuleSettings($this->core, $this->module, []);

        self::assertFalse(isset($command->{'core:missing'}));
    }
}