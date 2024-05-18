<?php

namespace PikaJew002\Handrolled\Database\Orm;

use BadMethodCallException;
use Closure;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\Expression\ExpressionBuilder;
use Doctrine\DBAL\Query\QueryBuilder as BaseQueryBuilder;
use PikaJew002\Handrolled\Database\Exceptions\InvalidComparisonOperatorException;
use PikaJew002\Handrolled\Database\Exceptions\InvalidSortExpressionException;
use PikaJew002\Handrolled\Database\Orm\Exceptions\BadQueryBuilderMethodCallException;
use PikaJew002\Handrolled\Database\Traits\UsesConnection;

class QueryBuilder
{
    use UsesConnection;

    protected array $comparisonOperators = [
        ExpressionBuilder::EQ,
        ExpressionBuilder::NEQ,
        ExpressionBuilder::LT,
        ExpressionBuilder::LTE,
        ExpressionBuilder::GT,
        ExpressionBuilder::GTE,
    ];
    protected array $sortOperators = [
        'ASC',
        'ASC NULLS FIRST',
        'ASC NULLS LAST',
        'DESC',
        'DESC NULLS FIRST',
        'DESC NULLS LAST',
    ];
    protected ?array $selects = null;
    protected ?string $table = null;
    protected ?string $alias = null;
    protected array $parameters = [];
    protected bool $whereDirty = false;

    protected BaseQueryBuilder $queryBuilder;

    protected ?string $entity = null;

    public function __construct(?Connection $db = null, ?BaseQueryBuilder $queryBuilder = null)
    {
        $db = $db ?? static::getDatabaseConnection();
        $this->queryBuilder = $queryBuilder ?? $db->createQueryBuilder();
    }

    public static function __callStatic($name, $arguments)
    {
        return (new static)->$name(...$arguments);
    }

    public function __call($name, $arguments)
    {
        if(method_exists($this, $name)) {
            return $this->$name(...$arguments);
        }

        throw new BadQueryBuilderMethodCallException($name);
    }

    protected function setEntity(?string $class): self
    {
        $this->entity = $class;

        return $this;
    }

    protected function insertInto(string $table, array $values): void
    {
        if(!is_null($this->entity)) {
            $this->entity::assertColumnsExist(array_keys($values));
        }
        foreach($values as $column => $value) {
            $this->queryBuilder->setValue($column, '?');
            $this->parameters[] = $value;
        }
        $this->queryBuilder->insert($table)->setParameters($this->parameters)->executeStatement();
    }

    protected function insertAndReturnIdInto(string $table, array $values): int
    {
        if(!is_null($this->entity)) {
            $this->entity::assertColumnsExist(array_keys($values));
        }
        foreach($values as $column => $value) {
            $this->queryBuilder->setValue($column, '?');
            $this->parameters[] = $value;
        }
        $this->queryBuilder->insert($table)->setParameters($this->parameters)->executeStatement();

        return (int) $this->queryBuilder->getConnection()->lastInsertId();
    }

    protected function insertAndReturnId(array $values): int
    {
        $table = $this->getTableNameFromQueryBuilder();
        $this->assertTableIsSet($table, 'insertAndReturnId');
        return $this->insertAndReturnIdInto($table, $values);
    }

    protected function insert(array $values): void
    {
        $table = $this->getTableNameFromQueryBuilder();
        $this->assertTableIsSet($table, 'insert');
        $this->insertInto($table, $values);
    }

    protected function update($table, array $values = []): void
    {
        if(is_array($table)) {
            $values = $table;
            $table = $this->getTableNameFromQueryBuilder();
            $class = static::class;
            $this->assertTableIsSet($table, function() use ($class) {
                return "Method `update` on class `{$class}` with one argument can only be called after a call to `table` method. Alternatively, pass the table name as the first argument and the values array as the second.";
            });
        }
        if(!is_null($this->entity)) {
            $this->entity::assertColumnsExist(array_keys($values));
        }
        $this->queryBuilder->update($table);
        $updateParams = [];
        foreach($values as $column => $value) {
            $this->queryBuilder->set($column, '?');
            $updateParams[] = $value;
        }
        array_unshift($this->parameters, ...$updateParams);
        $this->queryBuilder->setParameters($this->parameters)->executeStatement();
    }

    protected function deleteFrom(string $table): void
    {
        $this->queryBuilder->delete($table)->setParameters($this->parameters)->executeStatement();
    }

    protected function delete(): void
    {
        $table = $this->getTableNameFromQueryBuilder();
        $this->assertTableIsSet($table, 'delete');
        $this->deleteFrom($table);
    }

    protected function deleteWhere($column, $operator = null, $value = null, string $bool = 'and'): void
    {
        $this->where($column, $operator, $value, $bool)->delete();
    }

    protected function table(string $tableName, ?string $alias = null): self
    {
        $this->table = $tableName;
        $this->alias = $alias ?? $tableName;
        $this->queryBuilder->from($tableName, $this->alias);

        return $this;
    }

    private function assertTableIsSet($table, $method): void
    {
        if(!is_null($table)) {
            return;
        }
        if(is_string($method)) {
            $class = self::class;
            throw new BadMethodCallException("Method `{$method}` on class `{$class}` can only be called after a call to `table` method.");
        }
        if($method instanceof Closure) {
            throw new BadMethodCallException($method());
        }
    }

    protected function select($columns = null): self
    {
        if(is_null($columns)) {
            $this->selects = ['*'];
        } else if(func_num_args() === 1) {
            $columns = is_array($columns) ? $columns : [$columns];
            if(!is_null($this->entity)) {
                $this->entity::assertColumnsExist($columns);
            }
            $this->selects = $columns;
        } else {
            $selects = func_get_args();
            if(!is_null($this->entity)) {
                $this->entity::assertColumnsExist($selects);
            }
            $this->selects = $selects;
        }

        return $this;
    }

    protected function whereRaw(string $whereStatement, array $parameters = [], string $bool = 'and'): self
    {
        foreach($parameters as $parameter) {
            $this->parameters[] = $parameter;
        }

        return $this->addWhereToParent($whereStatement, $bool);
    }

    protected function where($column, $operator = null, $value = null, string $bool = 'and'): self
    {
        if($column instanceof Closure) {
            $query = new static($this->queryBuilder->getConnection());
            $column($query);
            $this->parameters = array_merge($this->parameters, $query->getParameters());
            $condition = (string) ($query->getQueryBuilder()->getQueryPart('where') ?? '');

            return $this->addWhereToParent($condition, $bool);
        }
        if(!is_null($this->entity)) {
            $this->entity::assertColumnsExist([$column]);
        }
        $condition = $this->buildConditionAddValueParameter($column, $operator, $value);

        return $this->addWhereToParent($condition, $bool);
    }

    protected function orWhere($column, $operator = null, $value = null): self
    {
        return $this->where($column, $operator, $value, 'or');
    }

    protected function whereIn(string $column, $inSet, string $bool = 'and'): self
    {
        if(!is_null($this->entity)) {
            $this->entity::assertColumnsExist([$column]);
        }
        $inSet = (array) $inSet;
        foreach($inSet as $item) {
            $this->parameters[] = $item;
        }
        $inSetPlaceholder = array_fill(0, count($inSet), '?');
        $condition = $this->queryBuilder->expr()->in($column, $inSetPlaceholder);

        return $this->addWhereToParent($condition, $bool);
    }

    public function innerJoin(string $joinTable, string $column, $operator = null, $value = null): self
    {
        $condition = (is_null($operator) && is_null($value)) ? $column : $this->buildConditionAddValueParameter($column, $operator, $value);
        $this->queryBuilder->innerJoin($this->alias, $joinTable, $joinTable, $condition);

        return $this;
    }

    public function leftJoin(string $joinTable, string $column, $operator = null, $value = null): self
    {
        $condition = (is_null($operator) && is_null($value)) ? $column : $this->buildConditionAddValueParameter($column, $operator, $value);
        $this->queryBuilder->leftJoin($this->alias, $joinTable, $joinTable, $condition);

        return $this;
    }

    public function rightJoin(string $joinTable, string $column, $operator = null, $value = null): self
    {
        $condition = (is_null($operator) && is_null($value)) ? $column : $this->buildConditionAddValueParameter($column, $operator, $value);
        $this->queryBuilder->rightJoin($this->alias, $joinTable, $joinTable, $condition);

        return $this;
    }

    protected function orderBy($columns, $order = null): self
    {
        $columns = (array) $columns;
        if(!is_null($this->entity)) {
            $this->entity::assertColumnsExist($columns);
        }
        $order = $order ?? 'ASC';
        $this->assertSortOperatorExists($order);
        foreach($columns as $column) {
            $this->queryBuilder->addOrderBy($column, $order);
        }

        return $this;
    }

    protected function groupBy($columns): self
    {
        $columns = (array) $columns;
        if(!is_null($this->entity)) {
            $this->entity::assertColumnsExist($columns);
        }
        foreach($columns as $column) {
            $this->queryBuilder->addGroupBy($column);
        }

        return $this;
    }

    public function get()
    {
        if(is_null($this->selects ?? null)) {
            $this->queryBuilder->select('*');
        } else {
            $this->queryBuilder->select(...$this->selects);
        }

        $array = $this->queryBuilder->setParameters($this->parameters)->fetchAllAssociative();
        
        if(!is_null($this->entity)) {
            return array_map(function ($row) {
                return $this->entity::morph($row);
            }, $array);
        }

        return $array;
    }

    public function first()
    {
        return $this->get()[0] ?? null;
    }

    private function buildConditionAddValueParameter($column, $operator = null, $value = null): string
    {
        if(!$this->isValidComparisonOperator($operator)) {
            if(!is_null($value)) {
                throw new InvalidComparisonOperatorException($operator);
            }
            $value = $operator;
            $operator = ExpressionBuilder::EQ;
        }
        $condition = $this->queryBuilder->expr()->comparison($column, $operator, '?');
        $this->parameters[] = $value;

        return $condition;
    }

    private function addWhereToParent($where, $bool = 'and'): self
    {
        if(!$this->whereDirty) {
            $this->queryBuilder->where($where);
        } else if(strtolower($bool) === 'or') {
            $this->queryBuilder->orWhere($where);
        } else {
            $this->queryBuilder->andWhere($where);
        }
        $this->whereDirty = true;

        return $this;
    }

    private function isValidComparisonOperator($operator): bool
    {
        return !is_null($operator) && in_array($operator, $this->comparisonOperators, true);
    }

    private function assertSortOperatorExists(string $order): void
    {
        if(!in_array($order, $this->sortOperators)) {
            throw new InvalidSortExpressionException($order);
        }
    }

    private function getTableNameFromQueryBuilder(): ?string
    {
        $table = $this->queryBuilder->getQueryPart('from');
        if(array_key_exists(0, $table)) {
            $table = $table[0];
            if(array_key_exists('table', $table)) {
                $table = $table['table'];
            }
        }

        return empty($table) ? null : $table;
    }

    public function getQueryBuilder(): BaseQueryBuilder
    {
        return $this->queryBuilder;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }
}
