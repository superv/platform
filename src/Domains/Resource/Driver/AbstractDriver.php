<?php

namespace SuperV\Platform\Domains\Resource\Driver;

use SuperV\Platform\Domains\Resource\Builder\PrimaryKey;

abstract class AbstractDriver implements DriverInterface
{
    protected $type = 'database';

    /**
     * @var array
     */
    protected $primaryKeys = [];

    /**
     * @var array
     */
    protected $params;

    public function primaryKey(PrimaryKey $key): DriverInterface
    {
        $this->primaryKeys[$key->getName()] = $key;

        $this->setParam('primary_keys', $this->primaryKeys);

        return $this;
    }

    public function getPrimaryKey(string $keyName): ?PrimaryKey
    {
        return $this->primaryKeys[$keyName] ?? null;
    }

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

    public function getPrimaryKeys(): array
    {
        return $this->primaryKeys;
    }

    /** * @return static */
    public static function resolve()
    {
        return app(static::class);
    }
}