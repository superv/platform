<?php

namespace SuperV\Platform\Support\Meta;

use ArrayAccess;

class Meta implements ArrayAccess
{
    /**
     * @var string
     */
    protected $uuid;

    /**
     * @var array
     */
    protected $data = [];

    public function __construct($data = null, ?string $uuid = null)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $this->offsetSet($key, $value);
            }
        }
        $this->uuid = $uuid ?? uuid();
    }

    public function has($key): bool
    {
        return array_key_exists($key, $this->data);
    }

    public function set($key, $value = null)
    {
        if (count($keys = explode('.', $key)) === 1) {
            $this->offsetSet($key, $value);
        } else {
            $this->offsetSet(array_shift($keys), $item = new Meta);
            $item->set(implode('.', $keys), $value);
        }
    }

    public function get($key, $default = null)
    {
        if (count($keys = explode('.', $key)) === 1) {
            if ($data = $this->data[$key] ?? null) {
                return $data instanceof Meta ? $data->compose() : $data;
            }

            return $default;
        }

        if ($data = $this->data[$subKey = array_shift($keys)] ?? null) {
            return $data->get(implode('.', $keys));
        }
    }

    public function data()
    {
        return $this->data;
    }

    public function compose()
    {
        if (! $this->data) {
            return [];
        }
        foreach ($this->data as $key => $data) {
            $all[$key] = $data instanceof Meta ? $data->compose() : $data;
        }

        return $all ?? [];
    }

    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    public function offsetGet($offset)
    {
        return $this->data[$offset] ?? null;
    }

    public function offsetSet($offset, $value)
    {
        $this->data[$offset] = is_array($value) ? new Meta($value) : $value;
        $this->data = array_filter_null($this->data);
    }

    public function offsetUnset($offset)
    {
        $this->offsetSet($offset, null);
    }

    public function uuid(): ?string
    {
        return $this->uuid;
    }
}