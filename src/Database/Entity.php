<?php

namespace PikaJew002\Handrolled\Database;

use PikaJew002\Handrolled\Application\Application;
use PikaJew002\Handrolled\Traits\UsesContainer;
use Exception;
use PDO;
use ReflectionClass;
use ReflectionProperty;

abstract class Entity
{
    use UsesContainer;

    public static function getDbInstance()
    {
        return static::getContainer()->get('db');
    }
    /*
     * @return Entity
     */
    public static function morph(array $object)
    {
        $class = new ReflectionClass(get_called_class());
        $entity = $class->newInstance();
        foreach($class->getProperties(ReflectionProperty::IS_PUBLIC) as $prop) {
            if(isset($object[$prop->getName()])) {
                $prop->setValue($entity, $object[$prop->getName()]);
            }
        }
        $entity->initialize();

        return $entity;
    }

    /*
     * @return Entity[]
     * @param $options = ["conditions" => [...], "order" => "..."]
     */
    public static function find($options)
    {
        $db = static::getDbInstance();
        $tableName = static::getTableName();
        $result = [];
        $query = "SELECT * FROM `".$tableName."`";
        // options is array to parse into conditions/sort by
        if(is_array($options) && !empty($options)) {
            if(isset($options['conditions']) && !empty($options['conditions'])) {
                $conditions = [];
                foreach($options['conditions'] as $key => $value) {
                    $conditions[] = "`".$key."` = \"".$value."\"";
                }
                $query .=" WHERE ".implode(" AND ", $conditions);
            }
            if(isset($options['order']) && !empty($options['order'])) {
                $query .= " ORDER BY ".$options['order'];
            }
        }
        // options is an id to retreive a single entity
        else {
            $query .=" WHERE `id` = \"".$options."\"";
        }
        $raw = $db->query($query);
        if($db->errorCode() != 00000) {
            throw new Exception($db->errorInfo()[2]);
        }
        foreach($raw as $rawRow) {
            $result[] = static::morph($rawRow);
        }

        return $result;
    }

    /*
     * @return Entity[]
     */
    public function all()
    {
        $db = static::getDbInstance();
        $tableName = static::getTableName();
        $result = [];
        $query = "SELECT * FROM `".$tableName."`";
        $raw = $db->query($query);
        if($db->errorCode() != 00000) {
            throw new Exception($db->errorInfo()[2]);
        }
        foreach($raw as $rawRow) {
            $result[] = static::morph($rawRow);
        }

        return $result;
    }

    public function save()
    {
        $db = static::getDbInstance();
        $tableName = static::getTableName();
        $class = new ReflectionClass($this);
        $propsArray = [];
        foreach($class->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            $propName = $property->getName();
            if($propName !== "id") {
                if(!empty($this->{$propName})) {
                    $propsArray[] = "`".$propName."` = \"".$this->{$propName}."\"";
                }
            }
        }
        $setClause = implode(", ", $propsArray);
        $sqlQuery = "";
        if($this->id > 0) { // update query
            $sqlQuery = "UPDATE `".$tableName."` SET ".$setClause." WHERE id = ".$this->id;
        } else { // insery query
            $sqlQuery = "INSERT INTO `".$tableName."` SET ".$setClause;
        }
        $result = $db->exec($sqlQuery);
        if($db->errorCode() != 00000) {
            throw new Exception($db->errorInfo()[2]);
        }

        return $result;
    }

    public function delete()
    {
        $db = static::getDbInstance();
        $tableName = static::getTableName();
        $sqlQuery = "";
        if($this->id > 0) {
            $sqlQuery = "DELETE FROM `".$tableName."` WHERE id = ".$this->id;
        }
        $result = $db->exec($sqlQuery);
        if($db->errorCode() != 00000) {
            throw new Exception($db->errorInfo()[2]);
        }

        return $result;
    }
}
