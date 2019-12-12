<?php

namespace SuperV\Platform\Domains\Resource\Driver;

use Doctrine\DBAL\Schema\Table;
use SuperV\Platform\Domains\Database\Schema\SchemaService;
use SuperV\Platform\Domains\Resource\Builder\Blueprint;
use SuperV\Platform\Domains\Resource\Builder\FieldBlueprint;
use SuperV\Platform\Domains\Resource\Builder\RelationBlueprint;

class DatabaseDriver extends AbstractDriver
{
    /**
     * @var \Doctrine\DBAL\Schema\Table
     */
    protected $table;

    /**
     * @var string
     */
    protected $connection;

    /**
     * @var string
     */
    protected $model;


    public function run(Blueprint $blueprint)
    {
//        dd(SchemaService::resolve()->getConnection()->getDatabasePlatform()->type);

        $this->table = new Table($this->getParam('table'));

        $keys = [];
        /** @var \SuperV\Platform\Domains\Resource\Builder\PrimaryKey $primaryKey */
        foreach ($this->primaryKeys as $primaryKey) {
            $this->table->addColumn($primaryKey->getName(), $primaryKey->getType(), $primaryKey->getOptions());

            $keys[] = $primaryKey->getName();
        }

        $blueprint->getFields()->map(function (FieldBlueprint $field) {
            $field->getField()->getFieldType()->driverCreating($this, $field);
        });

        $blueprint->getRelations()->map(function (RelationBlueprint $relation) {
            $relation->getRelation()->driverCreating($relation, $this);
        });

        $this->table->setPrimaryKey($keys);

        SchemaService::resolve()->createTable($this->table);
    }

    public function getTable(): Table
    {
        return $this->table;
    }

    public function table(string $table, string $connection = 'default'): DatabaseDriver
    {
        $this->setParam('table', $table);
        $this->setParam('connection', $connection);

        return $this;
    }

    public function model(string $model): self
    {
        $this->setParam('model', $model);

        return $this;
    }

}