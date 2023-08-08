<?php

declare(strict_types=1);

namespace Saloon\Helpers;

use Closure;
use ReflectionClass;
use Saloon\Contracts\Connector;
use Saloon\Contracts\PendingRequest;
use Saloon\Contracts\Request;

class Helpers
{
    /**
     * Returns all traits used by a class, its parent classes and trait of their traits.
     *
     * @param object|class-string $class
     * @return array<class-string, class-string>
     */
    public static function classUsesRecursive(object|string $class): array
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        $results = [];

        foreach (array_reverse(class_parents($class)) + [$class => $class] as $class) {
            $results += static::traitUsesRecursive($class);
        }

        return array_unique($results);
    }

    /**
     * Returns all traits used by a trait and its traits.
     *
     * @param class-string $trait
     * @return array<class-string, class-string>
     */
    public static function traitUsesRecursive(string $trait): array
    {
        /** @var array<class-string, class-string> $traits */
        $traits = class_uses($trait) ?: [];

        foreach ($traits as $trait) {
            $traits += static::traitUsesRecursive($trait);
        }

        return $traits;
    }

    /**
     * Return the default value of the given value.
     *
     * @param mixed $value
     * @param mixed ...$args
     * @return mixed
     */
    public static function value(mixed $value, mixed ...$args): mixed
    {
        return $value instanceof Closure ? $value(...$args) : $value;
    }

    /**
     * Check if a class is a subclass of another.
     *
     * @param class-string $class
     * @param string $subclass
     * @return bool
     * @throws \ReflectionException
     */
    public static function isSubclassOf(string $class, string $subclass): bool
    {
        if ($class === $subclass) {
            return true;
        }

        return (new ReflectionClass($class))->isSubclassOf($subclass);
    }

    /**
     * Boot a plugin
     *
     * @param \Saloon\Contracts\PendingRequest $pendingRequest
     * @param \Saloon\Contracts\Connector|\Saloon\Contracts\Request $resource
     * @param string $trait
     * @return void
     * @throws \ReflectionException
     */
    public static function bootPlugin(PendingRequest $pendingRequest, Connector|Request $resource, string $trait): void
    {
        $traitReflection = new ReflectionClass($trait);

        $bootMethodName = 'boot' . $traitReflection->getShortName();

        if (! method_exists($resource, $bootMethodName)) {
            return;
        }

        $resource->{$bootMethodName}($pendingRequest);
    }
}
