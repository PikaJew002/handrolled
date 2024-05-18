<?php

use PikaJew002\Handrolled\Application\Application;
use PikaJew002\Handrolled\Database\Orm\Entity;
use PikaJew002\Handrolled\Database\Orm\QueryBuilder;
use PikaJew002\Handrolled\Database\Schema\Schema;
use PikaJew002\Handrolled\Database\Schema\Table;

beforeEach(function() {
    $this->app = new Application('./tests/artifacts', './tests/artifacts/config');
    $this->app->config([
        'database.driver' => 'sqlite',
    ]);
    $this->app->boot();
    Schema::create('parents', function(Table $table) {
        $table->id();
        $table->string('name');
        $table->timestamps();
    });
    Schema::create('children', function(Table $table) {
        $table->id();
        $table->integer('parent_id', ['notnull' => false, 'default' => null]);
        $table->string('name');
        $table->timestamps();

        $table->addForeignKeyConstraint('parents', ['parent_id'], ['id']);
    });
    Schema::create('belongs_to_many_1', function(Table $table) {
        $table->id();
        $table->string('name');
        $table->timestamps();
    });
    Schema::create('belongs_to_many_2', function(Table $table) {
        $table->id();
        $table->string('name');
        $table->timestamps();
    });
    Schema::create('belongs_to_many_join', function(Table $table) {
        $table->integer('belongs_to_many_1_id');
        $table->integer('belongs_to_many_2_id');
        $table->setPrimaryKey(['belongs_to_many_1_id', 'belongs_to_many_2_id']);
        $table->addForeignKeyConstraint('belongs_to_many_1', ['belongs_to_many_1_id'], ['id']);
        $table->addForeignKeyConstraint('belongs_to_many_2', ['belongs_to_many_2_id'], ['id']);
    });
});

beforeAll(function() {
    class ParentModel extends Entity
    {
        protected string $tableName = 'parents';
        protected string $primaryKey = 'id';
        protected bool $timestamps = true;

        public $id;
        public $name;
        public $created_at;
        public $updated_at;

        public function children(): array
        {
            return $this->hasMany(ChildModel::class, 'parent_id');
        }

        public function childrenWithExplictPrimaryKey(): array
        {
            return $this->hasMany(ChildModel::class, 'parent_id', 'id');
        }
    }
    class ChildModel extends Entity
    {
        protected string $tableName = 'children';
        protected string $primaryKey = 'id';
        protected bool $timestamps = true;

        public $id;
        public $parent_id;
        public $name;
        public $created_at;
        public $updated_at;

        public function parent(): ?object
        {
            return $this->belongsTo(ParentModel::class, 'parent_id');
        }

        public function parentWithExplictPrimaryKey(): ?object
        {
            return $this->belongsTo(ParentModel::class, 'parent_id', 'id');
        }
    }

    class BelongsToMany1Model extends Entity
    {
        protected string $tableName = 'belongs_to_many_1';
        protected bool $timestamps = true;

        public $id;
        public $name;
        public $created_at;
        public $updated_at;

        public function other(): array
        {
            return $this->belongsToMany(BelongsToMany2Model::class, 'belongs_to_many_join', 'belongs_to_many_1_id', 'belongs_to_many_2_id');
        }

        public function otherWithExplictPrimaryKeys(): array
        {
            return $this->belongsToMany(BelongsToMany2Model::class, 'belongs_to_many_join', 'belongs_to_many_1_id', 'belongs_to_many_2_id', 'id', 'id');
        }
    }
    class BelongsToMany2Model extends Entity
    {
        protected string $tableName = 'belongs_to_many_2';
        protected bool $timestamps = true;

        public $id;
        public $name;
        public $created_at;
        public $updated_at;

        public function other(): array
        {
            return $this->belongsToMany(BelongsToMany1Model::class, 'belongs_to_many_join', 'belongs_to_many_2_id', 'belongs_to_many_1_id');
        }

        public function otherWithExplictPrimaryKeys(): array
        {
            return $this->belongsToMany(BelongsToMany1Model::class, 'belongs_to_many_join', 'belongs_to_many_2_id', 'belongs_to_many_1_id', 'id', 'id');
        }
    }
});

it('has a "has many" relationship', function() {
    $parent = ParentModel::insert([
        'name' => 'TheOneWhereBelongsToModel',
    ]);

    $child1 = ChildModel::insert([
        'parent_id' => $parent->id,
        'name' => 'TheOneWhereBelongsToModel1',
    ]);

    $child2 = ChildModel::insert([
        'parent_id' => $parent->id,
        'name' => 'TheOneWhereBelongsToModel2',
    ]);

    $children = $parent->children();
    $children2 = $parent->childrenWithExplictPrimaryKey();

    // checks to see if passing the primary key as the third param
    // in the hasMany method works
    expect($children[0]->id)->toBe($children2[0]->id);
    expect($children[1]->id)->toBe($children2[1]->id);

    expect(count($children))->toBe(2);
    expect($children[0]->id)->toBeIn([$child1->id, $child2->id]);
    expect($children[1]->id)->toBeIn([$child1->id, $child2->id]);
    expect($children[0]->name)->toBeIn([$child1->name, $child2->name]);
    expect($children[1]->name)->toBeIn([$child1->name, $child2->name]);
});

it('has a "belongs to" relationship', function() {
    $parent = ParentModel::insert([
        'name' => 'parent name',
    ]);

    $child1 = ChildModel::insert([
        'parent_id' => $parent->id,
        'name' => 'child 1 name',
    ]);

    $child2 = ChildModel::insert([
        'parent_id' => null,
        'name' => 'child 2 name',
    ]);

    $child1Parent = $child1->parent();
    $child1Parent2 = $child1->parentWithExplictPrimaryKey();

    // checks to see if passing the primary key as the third param
    // in the belongsTo method works
    expect($child1Parent->id)->toBe($child1Parent2->id);

    $child2Parent = $child2->parent();
    $child2Parent2 = $child2->parentWithExplictPrimaryKey();

    // checks to see if passing the primary key as the third param
    // in the belongsTo method works
    expect($child2Parent)->toBe($child2Parent2);

    expect($child1Parent)->not->toBe(null);
    expect($child1Parent->id)->toBe($parent->id);
    expect($child1Parent->name)->toBe($parent->name);
    expect($child2Parent)->toBe(null);
});

it('has a "belongs to many" relationship both ways', function() {
    $belongsToMany1 = BelongsToMany1Model::insert([
        'name' => 'belongs to many 1 name',
    ]);

    $belongsToMany2 = BelongsToMany2Model::insert([
        'name' => 'belongs to many 2 name',
    ]);

    $belongsToMany3 = BelongsToMany2Model::insert([
        'name' => 'belongs to many 3 name',
    ]);

    QueryBuilder::insertInto('belongs_to_many_join', [
        'belongs_to_many_1_id' => $belongsToMany1->id,
        'belongs_to_many_2_id' => $belongsToMany2->id,
    ]);

    QueryBuilder::insertInto('belongs_to_many_join', [
        'belongs_to_many_1_id' => $belongsToMany1->id,
        'belongs_to_many_2_id' => $belongsToMany3->id,
    ]);

    $relations1 = $belongsToMany1->other();
    $relations2 = $belongsToMany2->other();

    expect(count($relations1))->toBe(2);
    expect(count($relations2))->toBe(1);

    expect($relations1[0]->id)->toBeIn([$belongsToMany2->id, $belongsToMany3->id]);
    expect($relations1[1]->id)->toBeIn([$belongsToMany2->id, $belongsToMany3->id]);
    expect($relations2[0]->id)->toBe($belongsToMany1->id);

    $relations1 = $belongsToMany1->otherWithExplictPrimaryKeys();
    $relations2 = $belongsToMany2->otherWithExplictPrimaryKeys();

    expect(count($relations1))->toBe(2);
    expect(count($relations2))->toBe(1);

    expect($relations1[0]->id)->toBeIn([$belongsToMany2->id, $belongsToMany3->id]);
    expect($relations1[1]->id)->toBeIn([$belongsToMany2->id, $belongsToMany3->id]);
    expect($relations2[0]->id)->toBe($belongsToMany1->id);
});
