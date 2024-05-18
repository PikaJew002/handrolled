<?php

use PikaJew002\Handrolled\Application\Application;
use PikaJew002\Handrolled\Database\Exceptions\InvalidComparisonOperatorException;
use PikaJew002\Handrolled\Database\Exceptions\InvalidSortExpressionException;
use PikaJew002\Handrolled\Database\Orm\QueryBuilder;
use PikaJew002\Handrolled\Database\Query;
use PikaJew002\Handrolled\Database\Schema\Schema;
use PikaJew002\Handrolled\Database\Schema\Table;

beforeEach(function() {
    $this->app = new Application('./tests/artifacts', './tests/artifacts/config');
    $this->app->config([
        'database.driver' => 'sqlite',
    ]);
    $this->app->boot();
    Schema::create('foo', function(Table $table) {
        $table->id();
        $table->integer('col_1');
        $table->string('col_2', ['notnull' => null, 'default' => null]);
    });
    QueryBuilder::insertInto('foo', ['col_1' => 1]);
    QueryBuilder::insertInto('foo', ['col_1' => 10]);
    QueryBuilder::insertInto('foo', ['col_1' => 20]);
});

it('throws Exception for static method call on non-existent method', function() {
    QueryBuilder::nonMethod();
})->throws(BadMethodCallException::class);

it('throws Exception for method call on non-existent method', function() {
    QueryBuilder::table('foo')->nonMethod(['col_1' => 200]);
})->throws(BadMethodCallException::class);

it('inserts into table using insertInto method', function() {
    QueryBuilder::insertInto('foo', ['col_1' => 0]);
    $rows = QueryBuilder::table('foo')->where('col_1', 0)->get();

    expect(count($rows))->toBe(1);
    expect($rows[0]['col_1'])->toBe(0);
});

it('inserts into table using insertAndReturnIdInto method', function() {
    $id = QueryBuilder::insertAndReturnIdInto('foo', ['col_1' => 0]);
    $rows = QueryBuilder::table('foo')->where('col_1', 0)->get();

    expect(count($rows))->toBe(1);
    expect($rows[0]['id'])->toBe($id);
    expect($rows[0]['col_1'])->toBe(0);
});

it('throws Exception if table method is not called before insertAndReturnId method', function() {
    QueryBuilder::insertAndReturnId(['col_1' => 0]);
})->throws(BadMethodCallException::class);

it('inserts into table using insertAndReturnId method', function() {
    $id = QueryBuilder::table('foo')->insertAndReturnId(['col_1' => 0]);
    $rows = QueryBuilder::table('foo')->where('col_1', 0)->get();

    expect(count($rows))->toBe(1);
    expect($rows[0]['id'])->toBe($id);
    expect($rows[0]['col_1'])->toBe(0);
});

it('throws Exception if table method is not called before insert method', function() {
    QueryBuilder::insert(['col_1' => 0]);
})->throws(BadMethodCallException::class);

it('inserts into table using insert method', function() {
    QueryBuilder::table('foo')->insert(['col_1' => 0]);
    $rows = QueryBuilder::table('foo')->where('col_1', 0)->get();

    expect(count($rows))->toBe(1);
    expect($rows[0]['col_1'])->toBe(0);
});

it('updates table using update method with one arg', function() {
    QueryBuilder::where('col_1', 1)->update('foo', ['col_1' => 0]);
    $rows = QueryBuilder::table('foo')->where('col_1', 0)->get();

    expect(count($rows))->toBe(1);
    expect($rows[0]['col_1'])->toBe(0);
});

it('throws Exception if table method is not called before update method with one arg', function() {
    QueryBuilder::where('col_1', 1)->update(['col_1' => 0]);
})->throws(BadMethodCallException::class);

it('updates table using update method with two args', function() {
    QueryBuilder::table('foo')->where('col_1', 1)->update(['col_1' => 0]);
    $rows = QueryBuilder::table('foo')->where('col_1', 0)->get();

    expect(count($rows))->toBe(1);
    expect($rows[0]['col_1'])->toBe(0);
});

it('deletes from table using deleteFrom method', function() {
    QueryBuilder::where('col_1', 1)->deleteFrom('foo');
    $rows = QueryBuilder::table('foo')->where('col_1', 1)->get();

    expect(count($rows))->toBe(0);
});

it('throws Exception if table method is not called before delete method', function() {
    QueryBuilder::where('col_1', 1)->delete();
})->throws(BadMethodCallException::class);

it('deletes from table using delete method', function() {
    QueryBuilder::table('foo')->where('col_1', 1)->delete();
    $rows = QueryBuilder::table('foo')->where('col_1', 1)->get();

    expect(count($rows))->toBe(0);
});

it('deletes from table using deleteWhere method', function() {
    QueryBuilder::table('foo')->deleteWhere('col_1', 1);
    $rows = QueryBuilder::table('foo')->where('col_1', 1)->get();

    expect(count($rows))->toBe(0);
});

it('selects columns', function() {
    $rows = QueryBuilder::table('foo')->select('col_1')->get();

    expect(count($rows))->toBe(3);
    expect($rows[0]['col_1'])->toBeIn([1, 10, 20]);

    $rows = QueryBuilder::table('foo')->select('col_1', 'col_2')->get();

    expect(count($rows))->toBe(3);
    expect($rows[0]['col_1'])->toBeIn([1, 10, 20]);
    expect($rows[0]['col_2'])->toBeNull();
});

it('filters results with nested where', function() {
    $rows = QueryBuilder::table('foo')->where(function(QueryBuilder $query) {
        $query->where('col_1', 1)->orWhere('col_1', 20);
    })->where('col_1', '<>', 10)->get();

    expect(count($rows))->toBe(2);
    expect($rows[0]['col_1'])->toBeIn([1, 20]);
    expect($rows[1]['col_1'])->toBeIn([1, 20]);
});

it('filters results where =', function() {
    $rows = QueryBuilder::table('foo')->where('col_1', '=', 10)->get();

    expect(count($rows))->toBe(1);
    expect($rows[0]['col_1'])->toBe(10);

    $rows = QueryBuilder::table('foo')->where('col_1', 10)->get();

    expect(count($rows))->toBe(1);
    expect($rows[0]['col_1'])->toBe(10);
});

it('filters results where <>', function() {
    $rows = QueryBuilder::table('foo')->where('col_1', '<>', 10)->get();

    expect(count($rows))->toBe(2);
    expect($rows[0]['col_1'])->toBeIn([1, 20]);
    expect($rows[1]['col_1'])->toBeIn([1, 20]);
});

it('filters results where >', function() {
    $rows = QueryBuilder::table('foo')->where('col_1', '>', 10)->get();

    expect(count($rows))->toBe(1);
    expect($rows[0]['col_1'])->toBe(20);
});

it('filters results where >=', function() {
    $rows = QueryBuilder::table('foo')->where('col_1', '>=', 10)->get();

    expect(count($rows))->toBe(2);
    expect($rows[0]['col_1'])->toBeIn([10, 20]);
    expect($rows[1]['col_1'])->toBeIn([10, 20]);
});

it('filters results where <', function() {
    $rows = QueryBuilder::table('foo')->where('col_1', '<', 10)->get();

    expect(count($rows))->toBe(1);
    expect($rows[0]['col_1'])->toBe(1);
});

it('filters results where <=', function() {
    $rows = QueryBuilder::table('foo')->where('col_1', '<=', 10)->get();

    expect(count($rows))->toBe(2);
    expect($rows[0]['col_1'])->toBeIn([1, 10]);
    expect($rows[1]['col_1'])->toBeIn([1, 10]);
});

it('filters results with multiple where statements', function() {
    $rows = QueryBuilder::table('foo')->where('col_1', '<', 5)->where('col_1', '=', 1)->get();

    expect(count($rows))->toBe(1);
    expect($rows[0]['col_1'])->toBe(1);
});

it('throws Exception if where comparison operator is invalid', function() {
    $rows = QueryBuilder::table('foo')->where('col_1', 'not an operator', 5)->get();
})->throws(InvalidComparisonOperatorException::class);

it('filters results with orWhere statement', function() {
    $rows = QueryBuilder::table('foo')->where('col_1', '<', 5)->orWhere('col_1', '>', 15)->get();

    expect(count($rows))->toBe(2);
    expect($rows[0]['col_1'])->toBeIn([1, 20]);
    expect($rows[1]['col_1'])->toBeIn([1, 20]);
});

it('filters results with whereIn statement', function() {
    $rows = QueryBuilder::table('foo')->whereIn('col_1', ['1', '20'])->get();

    expect(count($rows))->toBe(2);
    expect($rows[0]['col_1'])->toBeIn([1, 20]);
    expect($rows[1]['col_1'])->toBeIn([1, 20]);
});

it('joins using leftJoin method', function() {
    Schema::create('foo2', function(Table $table) {
        $table->integer('foo2_col_1');
        $table->string('foo2_col_2', ['notnull' => null, 'default' => null]);
    });
    QueryBuilder::insertInto('foo2', ['foo2_col_1' => 1, 'foo2_col_2' => 'this is 1']);
    QueryBuilder::insertInto('foo2', ['foo2_col_1' => 10, 'foo2_col_2' => 'this is 10']);
    QueryBuilder::insertInto('foo2', ['foo2_col_1' => 20, 'foo2_col_2' => 'this is 20']);
    $results = QueryBuilder::table('foo')
              ->select(['foo.col_1', 'foo2.foo2_col_2'])
              ->leftJoin('foo2', 'foo2.foo2_col_1', '>', 10)
              ->get();

    expect(count($results))->toBe(3);
    expect($results[0]['foo2_col_2'])->toBe('this is 20');
    expect($results[1]['foo2_col_2'])->toBe('this is 20');
    expect($results[2]['foo2_col_2'])->toBe('this is 20');

    expect($results[0]['col_1'])->toBeIn([1, 10, 20]);
    expect($results[1]['col_1'])->toBeIn([1, 10, 20]);
    expect($results[2]['col_1'])->toBeIn([1, 10, 20]);
});

it('throws Exception if using rightJoin method if not supported', function() {
    Schema::create('foo2', function(Table $table) {
        $table->integer('foo2_col_1');
        $table->string('foo2_col_2', ['notnull' => null, 'default' => null]);
    });
    QueryBuilder::insertInto('foo2', ['foo2_col_1' => 1, 'foo2_col_2' => 'this is 1']);
    QueryBuilder::insertInto('foo2', ['foo2_col_1' => 10, 'foo2_col_2' => 'this is 10']);
    QueryBuilder::insertInto('foo2', ['foo2_col_1' => 20, 'foo2_col_2' => 'this is 20']);
    $results = QueryBuilder::table('foo')
              ->select(['foo.col_1', 'foo2.foo2_col_2'])
              ->rightJoin('foo2', 'foo.col_1', '>', 10)
              ->get();
})->throws(\Doctrine\DBAL\Exception\DriverException::class);

it('order results with orderBy', function() {
    $rows = QueryBuilder::table('foo')->orderBy('col_1', 'DESC')->get();

    expect(count($rows))->toBe(3);
    expect($rows[0]['col_1'])->toBe(20);
    expect($rows[1]['col_1'])->toBe(10);
    expect($rows[2]['col_1'])->toBe(1);
});

it('throws Exception if orderBy sort expression is invalid', function() {
    $rows = QueryBuilder::table('foo')->orderBy('col_1', 'DESC BY OTHER INVALID')->get();
})->throws(InvalidSortExpressionException::class);
