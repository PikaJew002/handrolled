<?php

namespace PikaJew002\Handrolled\Database\Schema;

use BadMethodCallException;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\DBAL\Schema\Table as BaseTable;

class Table
{
    protected BaseTable $table;
    protected AbstractPlatform $platform;

    public function __construct(BaseTable $table, AbstractPlatform $platform)
    {
        $this->table = $table;
        $this->platform = $platform;
    }

    public function __call($name, $arguments): self
    {
        $baseClass = BaseTable::class;
        if(method_exists($baseClass, $name)) {
            $this->table->$name(...$arguments);

            return $this;
        }
        $class = static::class;

        throw new BadMethodCallException("Method `{$name}` not found on class `{$class}` or class `{$baseClass}`");
    }

    public function string($name, array $options = [])
    {
        $this->table->addColumn($name, 'string', $options);
    }

    public function integer($name, array $options = [])
    {
        $this->table->addColumn($name, 'integer', $options);
    }

    public function id($name = 'id')
    {
        $this->table->addColumn($name, 'integer', ['autoincrement' => true]);
        $this->table->setPrimaryKey([$name]);
    }

    public function timestamps(array $columns = [])
    {
        $columns = array_merge(['created_at' => 'created_at', 'updated_at' => 'updated_at'], $columns);
        $default = "CURRENT_TIMESTAMP";
        $this->addColumn($columns['created_at'], 'datetime', ['default' => "CURRENT_TIMESTAMP"]);
        $this->addColumn($columns['updated_at'], 'datetime', ['default' => "CURRENT_TIMESTAMP"]);
    }
}
