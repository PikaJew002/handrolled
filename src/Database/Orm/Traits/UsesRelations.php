<?php

namespace PikaJew002\Handrolled\Database\Orm\Traits;

use PikaJew002\Handrolled\Database\Orm\QueryBuilder;
use ReflectionClass;

trait UsesRelations
{
    abstract protected static function getReflectionClass(): ReflectionClass;

    abstract public static function getTableName(?ReflectionClass $classReflect = null): string;

    abstract public static function morph(array $row, ?ReflectionClass $classReflect = null): self;

    abstract protected static function getProps(?ReflectionClass $classReflect = null): array;

    abstract public static function assertColumnsExist(array $columns, ?ReflectionClass $classReflect = null): void;

    abstract public static function getPrimaryKey(?ReflectionClass $classReflect = null): string;

    abstract public function getId(?ReflectionClass $classReflect = null);

    public function hasMany(string $relationClass, string $foreignKey, ?string $primaryKey = null): array
    {
        $classReflect = static::getReflectionClass();
        if(is_null($primaryKey)) {
          $id = $this->getId($classReflect);
        } else {
          static::assertColumnsExist([$primaryKey], $classReflect);
          $id = $this->{$primaryKey};
        }

        return (new $relationClass)->where($foreignKey, $id)->get();
    }

    public function belongsTo(string $relationClass, string $foreignKey, ?string $primaryKey = null): ?object
    {
        $relationClassReflect = $relationClass::getReflectionClass();
        if(is_null($primaryKey)) {
            $primaryKey = $relationClass::getPrimaryKey($relationClassReflect);
        } else {
            $relationClass::assertColumnsExist([$primaryKey], $relationClassReflect);
        }
        static::assertColumnsExist([$foreignKey]);
        $id = $this->{$foreignKey};
        $belongsTo = (new $relationClass)->where($primaryKey, $id)->get();

        return $belongsTo[0] ?? null;
    }

    public function belongsToMany(
        string $relationClass,
        string $joinTable,
        string $foreignKey,
        string $relationForeignKey,
        ?string $primaryKey = null,
        ?string $relationPrimaryKey = null
    ): array
    {
        $classReflect = static::getReflectionClass();
        $relationClassReflect = $relationClass::getReflectionClass();
        if(is_null($relationPrimaryKey)) {
            $relationPrimaryKey = $relationClass::getPrimaryKey($relationClassReflect);
        } else {
            $relationClass::assertColumnsExist([$relationPrimaryKey], $relationClassReflect);
        }
        if(is_null($primaryKey)) {
            $primaryKey = static::getPrimaryKey($classReflect);
        } else {
            static::assertColumnsExist([$primaryKey], $classReflect);
        }
        $relationTableName = $relationClass::getTableName($relationClassReflect);
        $properties = array_map(function($propery) use ($relationTableName) {
            return "{$relationTableName}.{$propery}";
        }, $relationClass::getProps($relationClassReflect));
        $results = QueryBuilder::table($joinTable)
            ->select(...$properties)
            ->innerJoin($relationTableName, "{$relationTableName}.{$relationPrimaryKey} = {$joinTable}.{$relationForeignKey}")
            ->whereRaw("{$joinTable}.{$foreignKey} = ?", [$this->{$primaryKey}])
            ->get();

        return array_map(function($entity) use ($relationClass) {
            return $relationClass::morph($entity);
        }, $results);
    }
}
