<?php

namespace ForkCMS\Core\Domain\Doctrine;

use InvalidArgumentException;
use Symfony\Component\DependencyInjection\Container;

trait ForkDBALTypeName
{
    public function getName(): string
    {
        $matches = [];
        if (
            preg_match(
                '/^ForkCMS\\\Modules\\\([A-Z][\w]*)\\\Domain\\\(?:([A-Z][\w]*)\\\)+\\\([A-Z][\w]*)DBALType$/',
                static::class,
                $matches
            )
        ) {
            return sprintf(
                'modules__%s__%s__%s',
                Container::underscore($matches[1]),
                Container::underscore($matches[2]),
                Container::underscore($matches[3])
            );
        }

        $matches = [];
        if (
            preg_match(
                '/^ForkCMS\\\Core\\\Domain\\\(?:([A-Z][\w]*)\\\)+\\\([A-Z][\w]*)DBALType$/',
                static::class,
                $matches
            )
        ) {
            return sprintf(
                'core__%s__%s',
                Container::underscore($matches[1]),
                Container::underscore($matches[2])
            );
        }

        throw new InvalidArgumentException('Cauld not automatically determine the unique DBAL type name');
    }
}
