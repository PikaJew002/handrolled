<?php

namespace PikaJew002\Handrolled\Database\Orm\Traits;

use ReflectionClass;
use ReflectionException;

trait UsesTimestamps
{
    abstract protected static function getReflectionClass(): ReflectionClass;

    // returns entity property $timestamps (bool) or false if not defined
    public static function usesTimestamps(?ReflectionClass $classReflect = null): bool
    {
        $classReflect = $classReflect ?? static::getReflectionClass();
        try {
            $classProperty = $classReflect->getProperty('timestamps');
            $type = $classProperty->getType();
            if(is_null($type) || !$classProperty->isProtected() || $type->getName() !== 'bool' || !$classProperty->isDefault()) {
                return false;
            }

            return true;
        } catch(\ReflectionException $e) {
            return false;
        }
    }

    // returns entity property name of the created_at timestamp
    protected function createdTimestampProperty(?ReflectionClass $classReflect = null): ?string
    {
        $classReflect = $classReflect ?? static::getReflectionClass();
        if(static::usesTimestamps($classReflect)) {
            if($classReflect->hasConstant('CREATED_AT') && $classReflect->hasProperty(static::CREATED_AT)) {
                return static::CREATED_AT;
            }
            return 'created_at';
        }

        return null;
    }

    protected function updatedTimestampProperty(?ReflectionClass $classReflect = null): ?string
    {
        $classReflect = $classReflect ?? static::getReflectionClass();
        if(static::usesTimestamps($classReflect)) {
            if($classReflect->hasConstant('UPDATED_AT') && $classReflect->hasProperty(static::UPDATED_AT)) {
                return static::UPDATED_AT;
            }
            return 'updated_at';
        }

        return null;
    }
}
