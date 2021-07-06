<?php

namespace ForkCMS\Core\Tests;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageFactoryInterface;
use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface;

final class MockArraySessionStorageFactory implements SessionStorageFactoryInterface
{
    public function createStorage(?Request $request): SessionStorageInterface
    {
        return new MockArraySessionStorage();
    }
}
