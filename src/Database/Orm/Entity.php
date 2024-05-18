<?php

namespace PikaJew002\Handrolled\Database\Orm;

use DateTime;
use PikaJew002\Handrolled\Database\Orm\Exceptions\MalformedEntityException;
use PikaJew002\Handrolled\Database\Orm\Exceptions\EntityPropertyNotFoundException;
use PikaJew002\Handrolled\Database\Orm\Exceptions\EntityNotFoundException;
use PikaJew002\Handrolled\Database\Orm\Traits\UsesRelations;
use PikaJew002\Handrolled\Database\Orm\Traits\UsesTimestamps;
use PikaJew002\Handrolled\Database\Traits\UsesConnection;
use ReflectionClass;
use ReflectionProperty;

abstract class Entity
{
    use UsesConnection, UsesTimestamps, UsesRelations;

    protected ?QueryBuilder $queryBuilder = null;

    public static function __callStatic($name, $arguments)
    {
        return (new static)->$name(...$arguments);
    }

    public function __call($name, $arguments)
    {
        if(method_exists($this, $name)) {
            return $this->$name(...$arguments);
        }

        return $this->getQueryBuilder()->$name(...$arguments);
    }

    protected static function getReflectionClass(): ReflectionClass
    {
        return new ReflectionClass(get_called_class());
    }

    public static function getTableName(?ReflectionClass $classReflect = null): string
    {
        $classReflect = $classReflect ?? static::getReflectionClass();
        $classProperties = $classReflect->getDefaultProperties();
        if(!isset($classProperties['tableName'])) {
            throw new MalformedEntityException($classReflect->getName());
        }

        return $classProperties['tableName'];
    }

    public static function getPrimaryKey(?ReflectionClass $classReflect = null): string
    {
        $classReflect = $classReflect ?? static::getReflectionClass();
        $classProperties = $classReflect->getDefaultProperties();
        $primaryKey = $classProperties['primaryKey'] ?? 'id';
        static::assertColumnsExist([$primaryKey], $classReflect);

        return $primaryKey;
    }

    public static function morph(array $row, ?ReflectionClass $classReflect = null): static
    {
        $classReflect = $classReflect ?? static::getReflectionClass();
        $entity = $classReflect->newInstance();
        foreach($classReflect->getProperties(ReflectionProperty::IS_PUBLIC) as $prop) {
            if(isset($row[$prop->getName()])) {
                $prop->setValue($entity, $row[$prop->getName()]);
            }
        }

        return $entity;
    }

    public function getId(?ReflectionClass $classReflect = null)
    {
        $primaryKey = static::getPrimaryKey($classReflect);

        return $this->{$primaryKey} ?? null;
    }

    protected function getQueryBuilder(): QueryBuilder
    {
        return $this->queryBuilder ?? $this->getFreshQueryBuilder();
    }

    protected function getFreshQueryBuilder(): QueryBuilder
    {
        return (new QueryBuilder(static::getDatabaseConnection()))->table(static::getTableName())->setEntity(static::class);
    }

    protected function insert(array $values): static
    {
        static::assertColumnsExist(array_keys($values));
        foreach($values as $propery => $value) {
            $this->{$propery} = $value;
        }
        $this->save();

        return $this;
    }

    protected function update(array $values): void
    {
        $this->getQueryBuilder()->update($values);
    }

    protected function save(): void
    {
        $classReflect = static::getReflectionClass();
        $primaryKey = static::getPrimaryKey($classReflect);
        $propertiesMap = [];
        foreach(static::getProps($classReflect) as $propName) {
            if($propName !== $primaryKey && !empty($this->{$propName})) {
                $propertiesMap[$propName] = $this->{$propName};
            }
        }

        $primaryKeyValue = $this->getId($classReflect);
        // do update
        if(!is_null($primaryKeyValue)) {
            $updatedAtProperty = $this->updatedTimestampProperty($classReflect);
            if(!is_null($updatedAtProperty)) {
                $propertiesMap[$updatedAtProperty] = (new DateTime)->format("Y-m-d H:i:s");
            }
            $this->getFreshQueryBuilder()->where($primaryKey, $primaryKeyValue)->update($propertiesMap);
        }
        // do insert
        else {
            $primaryKeyValue = $this->getFreshQueryBuilder()->insertAndReturnId($propertiesMap);
            $this->{$primaryKey} = $primaryKeyValue;
            $hydratedProperties = array_filter([$this->createdTimestampProperty($classReflect), $this->updatedTimestampProperty($classReflect)]);
            if(!empty($hydratedProperties)) {
                $databaseValues = $this->getFreshQueryBuilder()->setEntity(null)->select(...$hydratedProperties)->where($primaryKey, $primaryKeyValue)->first();
                $createdAt = $this->createdTimestampProperty($classReflect);
                if(!is_null($createdAt) && array_key_exists($createdAt, $databaseValues)) {
                    $this->{$createdAt} = $databaseValues[$createdAt];
                }
                $updatedAt = $this->updatedTimestampProperty($classReflect);
                if(!is_null($updatedAt) && array_key_exists($updatedAt, $databaseValues)) {
                    $this->{$updatedAt} = $databaseValues[$updatedAt];
                }
            }
        }
    }

    protected function delete(): void
    {
        $classReflect = static::getReflectionClass();
        $primaryKey = static::getPrimaryKey($classReflect);
        $primaryKeyValue = $this->getId($classReflect);
        if(is_null($primaryKeyValue)) {
            throw new EntityNotFoundException(static::getTableName($classReflect), $primaryKey, $primaryKeyValue);
        }
        $this->getFreshQueryBuilder()->deleteWhere($primaryKey, $primaryKeyValue);
    }

    /*
     * @return Entity[]
     */
    protected static function all(): array
    {
        return (new static)->getQueryBuilder()->select()->get();
    }

    protected static function find($id): ?static
    {
        return (new static)->where(static::getPrimaryKey(), $id)->first();
    }

    protected static function getProps(?ReflectionClass $classReflect = null): array
    {
        $classReflect = $classReflect ?? static::getReflectionClass();

        return array_map(function($item) {
                return $item->getName();
            },
            $classReflect->getProperties(ReflectionProperty::IS_PUBLIC)
        );
    }

    public static function assertColumnsExist(array $columns, ?ReflectionClass $classReflect = null): void
    {
        $properties = static::getProps($classReflect);
        foreach($columns as $column) {
            if(!in_array($column, $properties)) {
                throw new EntityPropertyNotFoundException($column, get_called_class());
            }
        }
    }

    public function toArray(): array
    {
        $properties = static::getProps();
        $values = [];
        foreach($properties as $property) {
            $values[$property] = $this->{$property};
        }

        return $values;
    }
}
