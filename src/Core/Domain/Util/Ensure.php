<?php

declare(strict_types=1);

namespace ForkCMS\Core\Domain\Util;

use Assert\Assertion;
use Assert\AssertionFailedException;

final class Ensure
{
    /**
     * @template T
     * @param T $value
     * @return T
     * @phpstan-assert !null $value
     * @throws AssertionFailedException
     */
    public static function isNotNull(
        mixed $value,
        string|callable|null $message = null,
        ?string $propertyPath = null
    ): mixed {
        Assertion::notNull($value, $message, $propertyPath);

        return $value;
    }

    /**
     * @template T of object
     * @param mixed $value
     * @param class-string<T> $className
     * @return T
     * @throws AssertionFailedException
     */
    public static function isInstanceOf(
        mixed $value,
        string $className,
        string|callable|null $message = null,
        ?string $propertyPath = null
    ): mixed {
        Assertion::isInstanceOf($value, $className, $message, $propertyPath);

        return $value;
    }

    /**
     * @phpstan-assert string $value
     * @throws AssertionFailedException
     */
    public static function isString(
        mixed $value,
        string|callable|null $message = null,
        ?string $propertyPath = null
    ): string {
        Assertion::string($value, $message, $propertyPath);

        return $value;
    }

    /**
     * @template T of object
     * @template I of object
     * @param T $value
     * @param class-string<I> $interfaceName
     * @return T&I
     * @phpstan-assert T&I $value
     * @throws AssertionFailedException
     */
    public static function isImplementingInterface(
        mixed $value,
        string $interfaceName,
        string|callable|null $message = null,
        ?string $propertyPath = null
    ): mixed {
        Assertion::implementsInterface($value, $interfaceName, $message, $propertyPath);

        return $value;
    }

    /**
     * @param class-string $className
     * @return class-string
     * @throws AssertionFailedException
     */
    public static function isExistingClass(
        string $className,
        string|callable|null $message = null,
        ?string $propertyPath = null
    ): string {
        Assertion::classExists($className, $message, $propertyPath);

        return $className;
    }

    /**
     * @throws AssertionFailedException
     */
    public static function isEmail(
        string $value,
        string|callable|null $message = null,
        ?string $propertyPath = null
    ): string {
        Assertion::email($value, $message, $propertyPath);

        return $value;
    }

    /**
     * @throws AssertionFailedException
     */
    public static function isUrl(
        string $value,
        string|callable|null $message = null,
        ?string $propertyPath = null
    ): string {
        Assertion::url($value, $message, $propertyPath);

        return $value;
    }

    /**
     * @throws AssertionFailedException
     */
    public static function isMatchingRegex(
        string $value,
        string $pattern,
        string|callable|null $message = null,
        ?string $propertyPath = null
    ): string {
        Assertion::regex($value, $pattern, $message, $propertyPath);

        return $value;
    }

    /**
     * @throws AssertionFailedException
     */
    public static function hasMaxLength(
        string $value,
        int $maxLength,
        string|callable|null $message = null,
        ?string $propertyPath = null
    ): string {
        Assertion::maxLength($value, $maxLength, $message, $propertyPath);

        return $value;
    }
}
