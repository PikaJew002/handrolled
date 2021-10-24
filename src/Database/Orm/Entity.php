<?php

namespace PikaJew002\Handrolled\Database\Orm;

use Exception;
use PikaJew002\Handrolled\Database\Orm\Exceptions\MalformedModelException;
use PikaJew002\Handrolled\Database\Orm\Exceptions\ModelPropertyNotFoundException;
use PikaJew002\Handrolled\Application\Application;
use PikaJew002\Handrolled\Interfaces\Database;
use PikaJew002\Handrolled\Traits\UsesContainer;
use ReflectionClass;
use ReflectionProperty;

abstract class Entity
{
    use UsesContainer;

    protected static string $connection = Database::class;

    public static function getDbInstance(): Database
    {
        return static::getContainer()->get(static::$connection);
    }

    public static function getTableName(): string
    {
        $classReflect = new ReflectionClass(get_called_class());
        $classProperties = $classReflect->getDefaultProperties();
        if(is_null(!isset($classProperties['tableName']))) {
            throw new MalformedModelException($classReflect->getName());
        }

        return $classProperties['tableName'];
    }

    public static function morph(array $object): self
    {
        $class = new ReflectionClass(get_called_class());
        $entity = $class->newInstance();
        foreach($class->getProperties(ReflectionProperty::IS_PUBLIC) as $prop) {
            if(isset($object[$prop->getName()])) {
                $prop->setValue($entity, $object[$prop->getName()]);
            }
        }

        return $entity;
    }

    /*
     * @return Entity[]
     * @param $options = ["conditions" => [...], "order" => "..."]
     */
    public static function find(array $options): array
    {
        $db = static::getDbInstance();
        $tableName = static::getTableName();
        $query = "SELECT * FROM $tableName";
        $params = [];
        // options is array to parse into conditions/sort by
        if(isset($options['conditions']) && !empty($options['conditions'])) {
            $conditions = [];
            foreach($options['conditions'] as $key => $value) {
                $conditions[] = "$key = ?";
                $params[] = $value;
            }
            $query .=" WHERE ".implode(" AND ", $conditions);
        }
        if(isset($options['order']) && !empty($options['order'])) {
            $query .= " ORDER BY {$options['order']}";
        }
        $raw = $db->prepare($query);
        $raw->execute($params);
        $result = [];
        foreach($raw as $rawRow) {
            $result[] = static::morph($rawRow);
        }

        return $result;
    }

    /*
     * @return Entity
     * @param $options = ["conditions" => [...], "order" => "..."]
     */
    public static function findById($id): ?self
    {
        $db = static::getDbInstance();
        $tableName = static::getTableName();
        $query = "SELECT * FROM $tableName WHERE id = ?";
        $raw = $db->prepare($query);
        $raw->execute([$id]);
        foreach($raw as $rawRow) {
            return static::morph($rawRow);
        }
        return null;
    }

    /*
     * @return Entity[]
     */
    public static function all()
    {
        $db = static::getDbInstance();
        $tableName = static::getTableName();
        $raw = $db->query("SELECT * FROM $tableName");
        $result = [];
        foreach($raw as $rawRow) {
            $result[] = static::morph($rawRow);
        }

        return $result;
    }

    public function save(): bool
    {
        $db = static::getDbInstance();
        $tableName = static::getTableName();
        $paramArray = [];
        $propsArray = [];
        foreach((new ReflectionClass($this))->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            $propName = $property->getName();
            if($propName !== 'id') {
                if(!empty($this->{$propName})) {
                    $paramArray[] = $this->{$propName};
                    $propsArray[] = "$propName = ?";
                }
            }
        }
        $setClause = implode(', ', $propsArray);
        if($this->id > 0) { // update query
            return $this->update($db, $tableName, $setClause, $paramArray);
        } else { // insery query
            return $this->insert($db, $tableName, $setClause, $paramArray);
        }
    }

    protected function update($db, string $tableName, string $setClause, array $params): bool
    {
        return $db->prepare("UPDATE $tableName SET $setClause, updated_at = NOW() WHERE id = ?")
                  ->execute(array_merge($params, [$this->id]));
    }

    protected function insert($db, string $tableName, string $setClause, array $params): bool
    {
        $db->prepare("INSERT INTO $tableName SET $setClause")->execute($params);
        if($this->id = $db->lastInsertId('id')) {
            foreach($db->query("SELECT created_at, updated_at FROM $tableName WHERE id = {$this->id}") as $result) {
                $this->created_at = $result['created_at'];
                $this->updated_at = $result['updated_at'];
            }
            return true;
        }
        return false;
    }

    public function delete(): bool
    {
        $db = static::getDbInstance();
        $tableName = static::getTableName();
        if($this->id > 0) {
            return $db->prepare("DELETE FROM $tableName WHERE id = ?")->execute([$this->id]);
        }
        return false;
    }

    protected static function getClassAndProps(): array
    {
        $classReflect = new ReflectionClass(get_called_class());

        $properties = array_map(function($item) {
                return $item->getName();
            },
            $classReflect->getProperties(ReflectionProperty::IS_PUBLIC)
        );

        return [$classReflect->getName(), $properties];
    }

    protected static function assertColumnsExist(string $className, array $columns, array $properties): void
    {
        assert(
            static::doColumnsExists($columns, $properties),
            new ModelPropertyNotFoundException($columnName, $className)
        );
    }

    protected static function doColumnsExists(array $columns, array $properties): bool
    {
        foreach($columns as $column) {
            if(!in_array($column, $properties)) {
                return false;
            }
        }
        return true;
    }

    public static function select($columns = []): QueryBuilder
    {
        [$className, $properties] = static::getClassAndProps();

        $columns = is_string($columns) ? [$columns] : $columns;

        static::assertColumnsExist($className, $columns, $properties);

        $queryBuilder = new QueryBuilder($className, static::getTableName(), static::getDbInstance());

        $queryBuilder->select($columns);

        return $queryBuilder;
    }

    public static function where(string $columnName, string $operator, $value, string $boolean = 'AND'): QueryBuilder
    {
        [$className, $properties] = static::getClassAndProps();

        static::assertColumnsExist($className, [$columnName], $properties);

        $queryBuilder = new QueryBuilder($className, static::getTableName(), static::getDbInstance());

        $queryBuilder->where($columnName, $operator, $value, $boolean);

        return $queryBuilder;
    }

    public static function whereEquals(string $columnName, $value)
    {
        $db = static::getDbInstance();
        $tableName = static::getTableName();
    }

    public static function whereGreaterThan(string $columnName, $value)
    {
        //
    }

    public static function whereLessThan(string $columnName, $value)
    {
        //
    }

    public static function whereLike(string $columnName, $value)
    {
        //
    }
}
