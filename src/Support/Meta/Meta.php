<?php

namespace SuperV\Platform\Support\Meta;

use ArrayAccess;
use SuperV\Platform\Contracts\Repository;
use SuperV\Platform\Domains\Resource\ResourceFactory;

class Meta implements Repository, ArrayAccess
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
        $this->items = [];
        $this->mergeItems($items);
    }

    public function mergeItems(array $items = [])
    {
        foreach ($items as $key => $value) {
            $this->items[$key] = new MetaValue($value);
        }
    }

    public function uuid(): string
    {
        return $this->uuid;
    }

    public function all(): array
    {
        $all = [];
        /**  @var  \SuperV\Platform\Support\Meta\MetaValue $data */
        foreach ($this->items as $key => $data) {
            $all[$key] = $data->getValue();
        }

        return $all;
    }

    public function has($key): bool
    {
        return array_key_exists($key, $this->items);
    }

    public function set($key, $value = null)
    {
        if (is_array($key)) {
            $this->mergeItems($key);
        } else {
            $this->items[$key] = new MetaValue($value);
        }
    }

    public function push($key, $value)
    {
        if (! $data = $this->get($key)) {
            $this->set($key, new Meta([$value]));
        } else {
            $data->push($key, $value);
        }
    }

    public function save()
    {
        $metaKeys = ResourceFactory::make('sv_meta_keys');

        foreach ($this->items as $key => $value) {
            $metaKeys->create([
                'uuid'  => $this->uuid(),
                'key'   => $key,
                'value' => $value,
            ]);
        }
    }

    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    public function offsetUnset($offset)
    {
        $this->set($offset, null);
    }

    public function get($key, $default = null)
    {
        $keys = explode('.', $key);
        if (count($keys) === 1) {
            if ($data = $this->items[$key] ?? null) {
                return $data->getValue();
            }

            return $default;
        }

        if ($data = $this->items[$subKey = array_shift($keys)] ?? null) {
            return $data->get(implode('.', $keys));
        }

//        $this->set($subKey, new Meta());
//
//        return $default;
    }

    public static function create(array $items = []): self
    {
        $meta = new Meta($items);
        $meta->save();

        return $meta;
    }

    public static function load(string $uuid)
    {
    }
}