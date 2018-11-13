<?php

namespace SuperV\Platform\Support\Meta;

use ArrayAccess;
use Exception;
use SuperV\Platform\Domains\Database\Model\BelongsToEntry;
use SuperV\Platform\Domains\Database\Model\Entry;
use SuperV\Platform\Domains\Database\Model\Morphable;

class Meta implements ArrayAccess, BelongsToEntry
{
    /** @var int */
    protected $id;

    /**
     * @var string
     */
    protected $uuid;

    /**
     * @var array
     */
    protected $data = [];

    /** @var array */
    protected $owner;

    /** @var \SuperV\Platform\Domains\Database\Model\Entry */
    protected $ownerEntry;

    protected $alwaysZip = false;

    public function __construct($data = [], ?string $uuid = null)
    {
        $this->data = $data;
        $this->uuid = $uuid ?? uuid();
    }

    public function has($key): bool
    {
        return array_key_exists($key, $this->data);
    }

    public function set($key, $value = null): self
    {
        if (count($keys = explode('.', $key)) === 1) {
            $this->offsetSet($key, $value);
        } else {
            $this->offsetSet(array_shift($keys), $item = new Meta);
            $item->set(implode('.', $keys), $value);
        }

        return $this;
    }

    public function get($key, $default = null)
    {
        if (count($keys = explode('.', $key)) === 1) {
            if ($data = $this->data[$key] ?? null) {
                return $data instanceof Meta ? $data->compose() : $data;
            }

            return $default;
        }

        if ($data = $this->data[array_shift($keys)] ?? null) {
            $subKey = implode('.', $keys);
            if (is_array($data)) {
                return (new Meta($data))->get($subKey);
            }

            return $data->get($subKey);
        }
    }

    public function zip(): self
    {
        foreach ($this->data as $key => $data) {
            $all[$key] = $data instanceof Meta ? $data->compose() : $data;
        }

        return new Meta($all ?? []);
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

    public function alwaysZip(bool $alwaysZip = true): Meta
    {
        $this->alwaysZip = $alwaysZip;

        return $this;
    }

    public function getOwner(): ?array
    {
        return $this->owner;
    }

    public function setOwner($ownerType, $ownerId = null, $label = null): Meta
    {
        if (is_object($ownerType)) {
            if ($ownerType instanceof Morphable) {
                $this->owner = [
                    'owner_type' => $ownerType->getOwnerType(),
                    'owner_id'   => $ownerType->getOwnerId(),
                ];
            } else {
                throw new Exception('Unknown owner type');
            }
        } else {
            $this->owner = ['owner_type' => $ownerType, 'owner_id' => $ownerId];
        }

//        $this->owner['label'] = $label;

        if ($ownerType instanceof Entry) {
            $this->ownerEntry = $ownerType;
        }

        return $this;
    }

    public function getOwnerEntry(): ?Entry
    {
        return $this->ownerEntry;
    }

    public function setOwnerEntry(Entry $entry)
    {
        $this->setOwner($entry);
    }

    public function id(): ?int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function uuid(): ?string
    {
        return $this->uuid;
    }

    public static function make($data = [], ?string $uuid = null): self
    {
        return new static($data, $uuid);
    }
}