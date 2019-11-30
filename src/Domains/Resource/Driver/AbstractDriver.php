<?php

namespace SuperV\Platform\Domains\Resource\Driver;

abstract class AbstractDriver implements DriverInterface
{
    protected $type = 'database';

    /**
     * @var array
     */
    protected $primaryKeys;

    /**
     * @var array
     */
    protected $params;

    public function primaryKey($name, $type = 'integer', array $options = []): DriverInterface
    {
        if ($type === 'integer' && empty($options)) {
            $options = ['unsigned' => true, 'autoincrement' => true];
        }

        $this->primaryKeys[] = array_filter(compact('name', 'type', 'options'));

        $this->setParam('primary_keys', $this->primaryKeys);

        return $this;
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

    /** * @return static */
    public static function resolve()
    {
        return app(static::class);
    }
}