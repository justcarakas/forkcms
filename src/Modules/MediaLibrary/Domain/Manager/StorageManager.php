<?php

namespace ForkCMS\Modules\MediaLibrary\Domain\Manager;

use ForkCMS\Modules\MediaLibrary\Domain\StorageProvider\StorageProviderInterface;
use ForkCMS\Modules\MediaLibrary\Domain\MediaItem\StorageType;
use Exception;

final class StorageManager
{
    /** @var array */
    protected $providers = [];

    public function addStorageProvider(StorageProviderInterface $storageProvider, string $storageType): void
    {
        $storageType = StorageType::fromString($storageType);

        // Add the storage provider
        $this->providers[$storageType->getStorageType()] = $storageProvider;
    }

    public function getStorageProvider(StorageType $storageType): StorageProviderInterface
    {
        if (array_key_exists($storageType->getStorageType(), $this->providers)) {
            return $this->providers[$storageType->getStorageType()];
        }

        $storageType = $storageType->getStorageType();
        throw new Exception(
            'MediaLibrary can\'t find any defined StorageProvider for the given storage type: "' . $storageType . '".'
        );
    }
}
