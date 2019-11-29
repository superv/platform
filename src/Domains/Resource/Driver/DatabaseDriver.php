<?php

namespace SuperV\Platform\Domains\Resource\Driver;

use Doctrine\DBAL\Schema\Table;
use SuperV\Platform\Domains\Database\Schema\SchemaService;
use SuperV\Platform\Domains\Resource\Blueprint\Blueprint;
use SuperV\Platform\Domains\Resource\Blueprint\FieldBlueprint;

class DatabaseDriver implements DriverInterface
{
    protected $type = 'database';

    /**
     * @var \Doctrine\DBAL\Schema\Table
     */
    protected $table;

    /**
     * @var string
     */
    protected $connection;

    /**
     * @var array
     */
    protected $primaryKeys;

    /**
     * @var array
     */
    protected $params;

    /**
     * @var string
     */
    protected $model;

    public function getParam($key)
    {
        return array_get($this->params, $key);
    }

    public function setParam($key, $value): DriverInterface
    {
        $this->params[$key] = $value;

        return $this;
    }

    public function toDsn(): string
    {
        return sprintf("%s@%s://%s", $this->getType(), $this->getParam('connection'), $this->getParam('table'));
    }

    public function getType()
    {
        return $this->type;
    }

    public function run(Blueprint $blueprint)
    {
        $this->table = new Table($this->getParam('table'));

        $keys = [];
        foreach ($this->primaryKeys as $primaryKey) {
            if ($primaryKey['type'] === 'integer') {
                $options = ['unsigned' => true, 'autoincrement' => $primaryKey['autoincrement'] ?? false];
            }
            $this->table->addColumn($primaryKey['name'], $primaryKey['type'], $options ?? []);
            $keys[] = $primaryKey['name'];
        }

        $this->table->setPrimaryKey($keys);

        $blueprint->getFields()->map(function (FieldBlueprint $field) {
            $field->getField()->getFieldType()->driverCreating($this);
        });

        SchemaService::resolve()->createTable($this->table);
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return [
            'type'   => $this->type,
            'params' => $this->params,
        ];
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

    public function primaryKey($name, $type = 'integer', $autoincrement = true): self
    {
        $this->primaryKeys[] = compact('name', 'type', 'autoincrement');

        $this->setParam('primary_keys', $this->primaryKeys);

        return $this;
    }

    /** * @return static */
    public static function resolve()
    {
        return app(static::class);
    }
}