<?php

use PikaJew002\Handrolled\Application\Application;
use PikaJew002\Handrolled\Database\Orm\QueryBuilder;
use PikaJew002\Handrolled\Database\Query;

beforeEach(function() {
    $this->app = new Application('./tests/artifacts', './tests/artifacts/config');
    $this->app->config([
        'database.driver' => 'sqlite',
    ]);
    $this->app->boot();
});

it('executes raw prepared statements', function() {
    Query::raw("CREATE TABLE foo(col_1 NUMBER, col_2 VARCHAR(10))");
    Query::raw("INSERT INTO foo values(?, ?)", [2, 'nine']);
    $rows = QueryBuilder::table('foo')->get();

    expect($rows[0]['col_1'])->toBe(2);
    expect($rows[0]['col_2'])->toBe('nine');

    Query::raw("UPDATE foo SET col_1 = ?, col_2 = ? WHERE col_1 = ?", [1, 'nine', 2]);
    $rows = QueryBuilder::table('foo')->get();

    expect($rows[0]['col_1'])->toBe(1);
    expect($rows[0]['col_2'])->toBe('nine');

    Query::raw("DELETE FROM foo WHERE col_1 = ? AND col_2 = ?", [1, 'nine']);
    $rows = QueryBuilder::table('foo')->get();

    expect($rows)->toBe([]);
});
