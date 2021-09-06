<?php

namespace PikaJew002\Handrolled\Database\Orm;

use PikaJew002\Handrolled\Database\Orm\Entity;
use PikaJew002\Handrolled\Database\Orm\Exceptions;

class QueryBuilder
{
    protected $db;
    protected string $class;
    protected string $table;
    protected array $selects;
    protected array $bindings = [
        'select' => [],
        'where' => [],
        'order_by' => [],
    ];
    protected array $selectColumns;
    protected array $wheres;
    protected array $operators = [
        '=',
        '>',
        '<',
        'LIKE',
    ];

    public function __construct(string $class, string $table, $db)
    {
        $this->db = $db;
        $this->class = $class;
        $this->table = $table;
        $this->selects = [];
        $this->wheres = [];
    }

    public function select(array $selects = []): self
    {
        $this->selects = $selects;
        return $this;
    }

    public function where(string $columnName, string $operator, $value, string $boolean = 'AND'): self
    {
        $this->assertOperatorExists($operator);

        $this->wheres[] = [
            'column' => $columnName,
            'operator' => $operator,
            'nested' => false, // for use later
            'boolean' => $boolean,
        ];

        $this->bindings['where'][] = $value;

        return $this;
    }

    protected function assertOperatorExists(string $operator): void
    {
        assert(
            in_array($operator, $this->operators),
            new Exceptions\ComparisonOperatorNotSupportedException($operator)
        );
    }

    protected function compileSelectToQueryString(): string
    {
        $selection = !empty($this->selects) ? implode(', ', $this->selects) : '*';
        $whereStatement = '';
        foreach($this->wheres as $key => $where) {
            if($key === 0) {
                $whereStatement .= '('.$where['column'].' '.$where['operator'].'?)';
            } else {
                $whereStatement .= ' '.$where['boolean'].' ('.$where['column'].' '.$where['operator'].' ?)';
            }
        }

        return 'SELECT '.$selection.' FROM '.$this->table.' WHERE '.$whereStatement;
    }

    protected function compileToBindingsArray(): array
    {
        $bindings = [];
        foreach($this->bindings as $key => $binding) {
            $bindings = array_merge($bindings, $binding);
        }

        return $bindings;
    }

    public function get(): array
    {
        $class = $this->class;
        $raw = $this->db->prepare($this->compileSelectToQueryString());
        $raw->execute($this->compileToBindingsArray());
        $result = [];
        foreach($raw as $rawRow) {
            $result[] = $class::morph($rawRow);
        }

        return $result;
    }
}
