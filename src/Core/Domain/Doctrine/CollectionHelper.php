<?php

namespace ForkCMS\Core\Domain\Doctrine;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

final class CollectionHelper
{
    public static function updateCollection(
        Collection $newCollection,
        Collection $currentCollection,
        callable $addCallback,
        callable $removeCallback
    ): void {
        $newCollection->map($addCallback);
        $currentCollection->filter(fn($item) => !$newCollection->contains($item))->map($removeCallback);
    }

    public static function toArrayCollection(Collection|null $collection): ArrayCollection
    {
        return new ArrayCollection($collection?->toArray() ?? []);
    }
}
