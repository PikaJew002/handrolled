<?php

use PikaJew002\Handrolled\Application\Application;
use PikaJew002\Handrolled\Database\Orm\Entity;
use PikaJew002\Handrolled\Database\Orm\Exceptions\BadQueryBuilderMethodCallException;
use PikaJew002\Handrolled\Database\Orm\Exceptions\EntityPropertyNotFoundException;
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
    class QueryEntity extends Entity
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

it('fetches all entities in table', function() {
    $model1 = new QueryEntity();
    $model1->name = 'Other Person';
    $model1->save();
    $model2 = new QueryEntity();
    $model2->name = 'Aaron Eisenberg';
    $model2->save();
    $model = QueryEntity::all();

    expect(count($model))->toBe(2);
    expect($model[0]->name)->toBeIn([$model1->name, $model2->name]);
    expect($model[1]->name)->toBeIn([$model1->name, $model2->name]);
});

it('finds entity in database', function() {
    $model1 = new QueryEntity();
    $model1->name = 'Aaron Eisenberg';
    $model1->save();
    $model = QueryEntity::where('name', '=', 'Aaron Eisenberg')->get();

    expect($model[0]->id)->toBe(1);
    expect($model[0]->name)->toBe('Aaron Eisenberg');
});

it('finds entity in database by primary key', function() {
    $model1 = new QueryEntity();
    $model1->name = 'Aaron Eisenberg';
    $model1->save();
    $model = QueryEntity::find(1);

    expect($model->id)->toBe(1);
    expect($model->name)->toBe('Aaron Eisenberg');
});

it('fails to find entity in database by non-existant primary key', function() {
    $model1 = new QueryEntity();
    $model1->name = 'Aaron Eisenberg';
    $model1->save();
    $model = QueryEntity::find(2);

    expect($model)->toBe(null);
});

it('selects only columns specified', function() {
    $model = new QueryEntity();
    $model->name = 'Some Name';
    $model->save();

    $models = QueryEntity::select('id', 'created_at', 'updated_at')->where('id', $model->id)->get();

    expect(count($models))->toBe(1);
    expect($models[0]->id)->toBe($model->id);
    expect($models[0]->created_at)->toBe($model->created_at);
    expect($models[0]->updated_at)->toBe($model->updated_at);
    expect($models[0]->name)->toBe(null);
});

it('selects all columns if none specified', function() {
    $model = new QueryEntity();
    $model->name = 'Some Name';
    $model->save();

    $models = QueryEntity::select()->where('id', $model->id)->get();

    expect(count($models))->toBe(1);
    expect($models[0]->id)->toBe($model->id);
    expect($models[0]->name)->toBe($model->name);
    expect($models[0]->created_at)->toBe($model->created_at);
    expect($models[0]->updated_at)->toBe($model->updated_at);
});

it('throws Exception if any columns selected do not exist on entity', function() {
    $model = new QueryEntity();
    $model->name = 'Some Name';
    $model->save();

    $models = QueryEntity::select('id', 'otehr_name', 'name', 'created_at', 'updated_at')->where('id', $model->id)->get();
})->throws(EntityPropertyNotFoundException::class);

it('finds entity in database and orders by column', function() {
    $model1 = new QueryEntity();
    $model1->name = 'Other Person';
    $model1->save();
    $model2 = new QueryEntity();
    $model2->name = 'Aaron Eisenberg';
    $model2->save();
    $model = QueryEntity::whereIn('id', [1, 2])->orderBy('name')->get();

    expect($model[0]->id)->toBe(2);
    expect($model[0]->name)->toBe('Aaron Eisenberg');
    expect($model[1]->id)->toBe(1);
    expect($model[1]->name)->toBe('Other Person');
});


it('finds entity in database and groups by column', function() {
    $model1 = new QueryEntity();
    $model1->name = 'Other Person';
    $model1->save();
    $model2 = new QueryEntity();
    $model2->name = 'Aaron Eisenberg';
    $model2->save();
    $model = QueryEntity::whereIn('id', [1, 2])->groupBy('name')->get();

    expect($model[0]->id)->toBe(2);
    expect($model[0]->name)->toBe('Aaron Eisenberg');
    expect($model[1]->id)->toBe(1);
    expect($model[1]->name)->toBe('Other Person');
});

it('throws Exception if non-existent method called', function() {
    (new QueryEntity())->nonExistentMethod();
})->throws(BadQueryBuilderMethodCallException::class);

it('throws Exception if non-existent static method called', function() {
    QueryEntity::nonExistentStaticMethod();
})->throws(BadQueryBuilderMethodCallException::class);
