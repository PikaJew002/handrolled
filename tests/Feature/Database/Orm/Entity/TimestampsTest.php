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
    Schema::create('models', function(Table $table) {
        $table->id();
        $table->string('name');
        $table->timestamps([
            'created_at' => 'created_at_timestamp',
            'updated_at' => 'updated_at_timestamp',
        ]);
    });
});

beforeAll(function() {
    class ModelWithTimestamps extends Entity
    {
        protected string $tableName = 'models';
        protected bool $timestamps = true;

        public const CREATED_AT = 'created_at_timestamp';
        public const UPDATED_AT = 'updated_at_timestamp';

        public $id;
        public $name;
        public $created_at_timestamp;
        public $updated_at_timestamp;
    }

    class ModelWithoutTimestamps extends Entity
    {
        protected string $tableName = 'models';

        public $id;
        public $name;
    }

    class ModelWithTimestampsPropertyMalformed extends Entity
    {
        protected string $tableName = 'models';
        protected $timestamps = true;

        public $id;
        public $name;
        public $created_at_timestamp;
        public $updated_at_timestamp;
    }

    class ModelWithTimestampsConstantsMalformed extends Entity
    {
        protected string $tableName = 'models';
        protected bool $timestamps = true;

        public $id;
        public $name;
        public $created_at;
        public $updated_at;
    }
});

it('uses timestamps and checks for existence of $timestamps propery', function() {
    $usesTimestamps = ModelWithoutTimestamps::usesTimestamps();

    expect($usesTimestamps)->toBe(false);
});

it('uses timestamps and checks type, visibility, and default value of $timestamps propery', function() {
    $usesTimestamps = ModelWithTimestampsPropertyMalformed::usesTimestamps();

    expect($usesTimestamps)->toBe(false);
});

it('uses timestamps and specifies the columns with the constants CREATED_AT and UPDATED_AT', function() {
    $model = new ModelWithTimestamps();
    $createdAtTimestamp = $model->createdTimestampProperty();
    $updatedAtTimestamp = $model->updatedTimestampProperty();

    expect($createdAtTimestamp)->toBe(ModelWithTimestamps::CREATED_AT);
    expect($updatedAtTimestamp)->toBe(ModelWithTimestamps::UPDATED_AT);
});

it('does not use timestamps', function() {
    $model = new ModelWithoutTimestamps();
    $createdAtTimestamp = $model->createdTimestampProperty();
    $updatedAtTimestamp = $model->updatedTimestampProperty();

    expect($createdAtTimestamp)->toBe(null);
    expect($updatedAtTimestamp)->toBe(null);
});
