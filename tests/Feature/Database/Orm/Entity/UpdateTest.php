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
    class UpdateModel extends Entity
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

it('updates model in database using update method', function() {
    $result1 = UpdateModel::insert([
        'name' => 'Another One',
    ]);

    expect($result1 instanceof UpdateModel)->toBe(true);

    UpdateModel::where('name', 'Another One')->update([
        'name' => 'Another Two',
    ]);

    $models = UpdateModel::where('name', 'Another Two')->get();

    expect(count($models))->toBe(1);
    expect($models[0]->name)->toBe('Another Two');
});

it('updates model in database using save method', function() {
    $model1 = new UpdateModel();
    $model1->name = 'Other Person';
    $model1->save();
    $model2 = UpdateModel::find(1);
    $model2->name = 'Different Person';
    $model2->save();
    $model3 = UpdateModel::find(1);

    expect($model3->name)->toBe('Different Person');
});
