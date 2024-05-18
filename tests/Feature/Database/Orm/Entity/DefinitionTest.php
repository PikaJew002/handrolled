<?php

use PikaJew002\Handrolled\Application\Application;
use PikaJew002\Handrolled\Database\Orm\Entity;
use PikaJew002\Handrolled\Database\Orm\Exceptions\MalformedEntityException;
use PikaJew002\Handrolled\Database\Schema\Schema;
use PikaJew002\Handrolled\Database\Schema\Table;

beforeEach(function() {
    $this->app = new Application('./tests/artifacts', './tests/artifacts/config');
    $this->app->config([
        'database.driver' => 'sqlite',
    ]);
    $this->app->boot();
    Schema::create('model', function(Table $table) {
        $table->id();
        $table->string('name');
        $table->string('col_2', ['notnull' => false, 'default' => null]);
        $table->timestamps();
    });
});

beforeAll(function() {
    class DefinitionModel extends Entity
    {
        protected string $tableName = 'model';
        protected string $primaryKey = 'id';
        protected bool $timestamps = true;

        public $id;
        public $name;
        public $created_at;
        public $updated_at;
    }
});



it('gets table name', function() {
    expect(DefinitionModel::getTableName())->toBe('model');
});

it('throws Exception if table name not defined', function() {
    class ModelWithNoTableNameDefined extends Entity {}
    ModelWithNoTableNameDefined::getTableName();
})->throws(MalformedEntityException::class);

it('gets primary key', function() {
    expect(DefinitionModel::getPrimaryKey())->toBe('id');
});
