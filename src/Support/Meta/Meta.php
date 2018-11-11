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
    protected $items;

    public function __construct(array $items = [])
    {
        $this->setItems($items);
        $this->uuid = uuid();
    }

    public function setItems(array $items = [])
    {
        foreach ($items as $key => $value) {
            $this->offsetSet($key, $value);
        }
    }

    public function has($key): bool
    {
        return array_key_exists($key, $this->items);
    }

    public function set($key, $value = null)
    {
        if (count($keys = explode('.', $key)) === 1) {
            return $this->offsetSet($key, $value);
        }

        $this->offsetSet(array_shift($keys), $item = new Meta);
        $item->set(implode('.', $keys), $value);
    }

    public function get($key, $default = null)
    {
        if (count($keys = explode('.', $key)) === 1) {
            if ($data = $this->items[$key] ?? null) {
                return $data instanceof Meta ? $data->all() : $data;
            }

            return $default;
        }

        if ($data = $this->items[$subKey = array_shift($keys)] ?? null) {
            return $data->get(implode('.', $keys));
        }
    }

    public function all(): array
    {
        foreach ($this->items as $key => $data) {
            $all[$key] = $data instanceof Meta ? $data->all() : $data;
        }

        return $all ?? [];
    }

    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    public function offsetGet($offset)
    {
        return $this->items[$offset] ?? null;
    }

    public function offsetSet($offset, $value)
    {
        $this->items[$offset] = is_array($value) ? new Meta($value) : $value;
    }

    public function offsetUnset($offset)
    {
        $this->offsetSet($offset, null);
    }

    public function uuid(): string
    {
        return $this->uuid;
    }
}