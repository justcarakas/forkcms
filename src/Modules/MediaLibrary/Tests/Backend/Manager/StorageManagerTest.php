<?php

namespace ForkCMS\Modules\MediaLibrary\Tests\Backend\Manager;

use ForkCMS\Modules\MediaLibrary\Domain\StorageProvider\LocalStorageProvider;
use ForkCMS\Modules\MediaLibrary\Domain\StorageProvider\VimeoStorageProvider;
use ForkCMS\Modules\MediaLibrary\Domain\StorageProvider\YoutubeStorageProvider;
use ForkCMS\Modules\MediaLibrary\Domain\MediaItem\StorageType;
use PHPUnit\Framework\TestCase;

class StorageManagerTest extends TestCase
{
    /** @var StorageManager */
    protected $storageManager;

    protected function setUp(): void
    {
        $this->storageManager = new StorageManager();
    }

    public function testLocalStorageProvider(): void
    {
        $cacheManager = $this->createMock('Liip\ImagineBundle\Imagine\Cache\CacheManager');
        $localStorageProvider = new LocalStorageProvider(
            'src/Frontend/Files/MediaLibrary',
            'https://www.website.be',
            'https://www.website.be',
            $cacheManager
        );

        $this->storageManager->addStorageProvider($localStorageProvider, 'local');
        self::assertEquals(
            $this->storageManager->getStorageProvider(StorageType::local()),
            $localStorageProvider
        );
    }

    public function testYoutubeStorageProvider(): void
    {
        $youtubeStorageProvider = new YoutubeStorageProvider(
            'https://www.youtube.com/watch?v=',
            'https://www.youtube.com/embed/'
        );
        $this->storageManager->addStorageProvider($youtubeStorageProvider, 'youtube');
        self::assertEquals(
            $this->storageManager->getStorageProvider(StorageType::youtube()),
            $youtubeStorageProvider
        );
    }

    public function testVimeoStorageProvider(): void
    {
        $vimeoStorageProvider = new VimeoStorageProvider(
            'https://www.vimeo.com/',
            'https://player.vimeo.com/video/'
        );
        $this->storageManager->addStorageProvider($vimeoStorageProvider, 'vimeo');
        self::assertEquals(
            $this->storageManager->getStorageProvider(StorageType::vimeo()),
            $vimeoStorageProvider
        );
    }
}
