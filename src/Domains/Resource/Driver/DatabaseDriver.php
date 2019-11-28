<?php

namespace SuperV\Platform\Domains\Resource\Driver;

use SuperV\Platform\Domains\Database\Schema\SchemaService;
use SuperV\Platform\Domains\Resource\Blueprint\Blueprint;

class DatabaseDriver implements DriverInterface
{
    protected $type = 'database';

    /** @var string */
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
        SchemaService::resolve()->createTable($this->getTable(), $this->primaryKeys);
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

    public function getTable(): string
    {
        return $this->getParam('table');
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