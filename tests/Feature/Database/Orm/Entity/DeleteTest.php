<?php

use PikaJew002\Handrolled\Application\Application;
use PikaJew002\Handrolled\Database\Orm\Entity;
use PikaJew002\Handrolled\Database\Orm\Exceptions\EntityNotFoundException;
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
    class DeleteModel extends Entity
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

it('deletes model in database', function() {
    $model = new DeleteModel();
    $model->name = 'Some Name';
    $model->save();

    expect($model->id)->not->toBe(null);

    $models = DeleteModel::where('name', 'Some Name')->get();

    expect(count($models))->toBe(1);
    expect($models[0]->name)->toBe('Some Name');

    $model->delete();
    $models = DeleteModel::where('name', 'Some Name')->get();

    expect(count($models))->toBe(0);
    expect($models)->toBe([]);
});

it('throws Exception when trying to delete model in database if not saved first', function() {
    $model = new DeleteModel();
    $model->name = 'Some Name';
    $models = DeleteModel::where('name', 'Some Name')->get();

    expect($model->id)->toBe(null);
    expect(count($models))->toBe(0);
    expect($models)->toBe([]);

    $model->delete();
})->throws(EntityNotFoundException::class);
