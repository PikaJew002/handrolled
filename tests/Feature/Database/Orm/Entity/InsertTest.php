<?php

use PikaJew002\Handrolled\Application\Application;
use PikaJew002\Handrolled\Database\Orm\Entity;
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
    class InsertModel extends Entity
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


it('creates new model and saves in database using insert method', function() {
    InsertModel::insert([
        'name' => 'Another One',
    ]);
    $models = InsertModel::where('name', 'Another One')->get();

    expect(count($models))->toBe(1);
    expect($models[0]->name)->toBe('Another One');
});

it('creates new model and saves in database using save method', function() {
    $model = new InsertModel();
    $model->name = 'Some Name';
    $model->save();
    $models = InsertModel::where('name', 'Some Name')->get();

    expect(count($models))->toBe(1);
    expect($models[0]->name)->toBe('Some Name');
});
