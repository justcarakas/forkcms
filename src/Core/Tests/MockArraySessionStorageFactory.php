<?php

namespace ForkCMS\Core\Tests;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageFactoryInterface;
use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface;

final class MockArraySessionStorageFactory implements SessionStorageFactoryInterface
{
    private static ?SessionStorageInterface $sessionStorage = null;

    public static function clearCurrentSession(): void
    {
        self::$sessionStorage = null;
    }

    public function createStorage(?Request $request): SessionStorageInterface
    {
        return self::$sessionStorage ?? self::$sessionStorage = new MockArraySessionStorage();
    }
}
