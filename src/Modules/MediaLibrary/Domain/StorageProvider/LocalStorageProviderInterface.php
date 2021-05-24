<?php

namespace ForkCMS\Modules\MediaLibrary\Domain\StorageProvider;

interface LocalStorageProviderInterface extends StorageProviderInterface, LiipImagineBundleStorageProviderInterface
{
    public function getUploadRootDir(): string;

    public function getWebDir(): string;
}
