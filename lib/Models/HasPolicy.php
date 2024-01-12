<?php

namespace Lernkarten\Models;

use ReflectionClass;
use RuntimeException;

trait HasPolicy
{
    public static function getPolicy()
    {
        $reflectionClass = new ReflectionClass(self::class);
        $class = '\\Lernkarten\\Policies\\' . $reflectionClass->getShortName() . 'Policy';
        if (!class_exists($class)) {
            throw new RuntimeException('Could not find Policy class.');
        }

        return new $class();
    }
}
