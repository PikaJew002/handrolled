<?php

namespace PikaJew002\Handrolled\Database\ORM;

use Exception;
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

    protected static function morph(array $object): self
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
    public static function find(array $options)
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
}
