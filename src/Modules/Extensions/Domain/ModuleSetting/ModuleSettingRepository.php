<?php

namespace ForkCMS\Modules\Extensions\Domain\ModuleSetting;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use ForkCMS\Modules\Extensions\Domain\Module\Module;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleName;
use InvalidArgumentException;
use Psr\Cache\CacheItemPoolInterface;
use RuntimeException;
use Symfony\Component\DependencyInjection\Container;
use Throwable;

/**
 * @method ModuleSetting|null find($id, $lockMode = null, $lockVersion = null)
 * @method ModuleSetting|null findOneBy(array $criteria, array $orderBy = null)
 * @method ModuleSetting[] findAll()
 * @method ModuleSetting[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class ModuleSettingRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private CacheItemPoolInterface $cache,
    ) {
        try {
            parent::__construct($registry, ModuleSetting::class);
        } catch (Throwable $throwable) {
            if (getenv('FORK_DATABASE_HOST')) {
                throw $throwable;
            }
        }
    }

    public function get(ModuleName $moduleName, string $name, mixed $defaultValue = null): mixed
    {
        $settings = $this->getSettingsFromCache();

        // don't use ?? since the setting value might also just be null
        if (isset($settings[$moduleName->getName()][$name])) {
            return $settings[$moduleName->getName()][$name];
        }

        return $defaultValue;
    }

    public function getForModule(ModuleName $moduleName): array
    {
        $settings = $this->getSettingsFromCache();

        if (isset($settings[$moduleName->getName()])) {
            return $settings[$moduleName->getName()];
        }

        // try again after clearing the cache
        $this->invalidateCache();
        $settings = $this->getSettingsFromCache();

        return $settings[$moduleName->getName()] ?? [];
    }

    public function set(ModuleName $moduleName, string $name, mixed $value): void
    {
        $moduleSetting = $this->findOneBy(['module' => $this->getModuleReference($moduleName), 'name' => $name]);
        if (!$moduleSetting instanceof ModuleSetting) {
            $this->saveModuleSetting(new ModuleSetting($this->getModuleReference($moduleName), $name, $value));
            $this->invalidateCache();

            return;
        }

        if ($moduleSetting->setValue($value)) {
            $this->saveModuleSetting($moduleSetting);
            $this->invalidateCache();
        }
    }

    public function delete(ModuleName $moduleName, string $name): void
    {
        $moduleSetting = $this->findOneBy(['module' => $this->getModuleReference($moduleName), 'name' => $name]);
        if (!$moduleSetting instanceof ModuleSetting) {
            return;
        }

        $this->removeModuleSetting($moduleSetting);
        $this->invalidateCache();
    }

    public function removeModuleSetting(ModuleSetting ...$moduleSettings): void
    {
        if (count($moduleSettings) === 0) {
            return;
        }

        $entityManager = $this->getEntityManager();
        foreach ($moduleSettings as $moduleSetting) {
            $entityManager->remove($moduleSetting);
        }
        $entityManager->flush();

        $this->invalidateCache();
    }

    public function saveModuleSetting(ModuleSetting ...$moduleSettings): void
    {
        if (count($moduleSettings) === 0) {
            return;
        }

        $entityManager = $this->getEntityManager();
        foreach ($moduleSettings as $moduleSetting) {
            $entityManager->persist($moduleSetting);
        }
        $entityManager->flush();

        $this->invalidateCache();
    }

    /**
     * Instead of invalidating the cache, we could also fetch existing
     * settings, update them & re-store them to cache. That would save
     * us the next query to repopulate the cache.
     * However, there could be race conditions where 2 concurrent
     * requests write at the same time and one ends up overwriting the
     * other (unless we do a CAS, but PSR-6 doesn't support that)
     * Clearing cache will be safe: in the case of concurrent requests
     * & cache being regenerated while the other is being saved, it will
     * be cleared again after saving the new setting!
     */
    private function invalidateCache(): void
    {
        $cacheKey = $this->getCacheKey();
        if ($this->cache->hasItem($cacheKey) && !$this->cache->deleteItem($cacheKey)) {
            throw new RuntimeException('Failed to clear the module settings cache');
        }
    }

    private function getModuleReference(ModuleName $moduleName): Module
    {
        $reference = $this->getEntityManager()->getPartialReference(Module::class, $moduleName);
        if ($reference instanceof Module) {
            return $reference;
        }

        throw new InvalidArgumentException('The module ' . $moduleName . ' is not installed');
    }

    private function getSettingsFromCache(): array
    {
        $item = $this->cache->getItem($this->getCacheKey());
        if ($item->isHit()) {
            return $item->get();
        }

        $groupedSettings = [];
        foreach ($this->findAll() as $setting) {
            $groupedSettings[$setting->getModule()->getName()->getName()][$setting->getname()] = $setting->getValue();
        }
        $item->set($groupedSettings);
        $this->cache->save($item);

        return $groupedSettings;
    }

    private function getCacheKey(): string
    {
        return str_replace('\\', '_', Container::underscore(self::class));
    }
}
