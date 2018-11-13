<?php

namespace SuperV\Platform\Support\Meta;

use SuperV\Platform\Domains\Database\Model\Entry;
use SuperV\Platform\Domains\Resource\ResourceFactory;

class Repository implements \SuperV\Platform\Domains\Database\Model\Repository
{
    /** @var \SuperV\Platform\Domains\Resource\Resource */
    protected $metas;

    /** @var \SuperV\Platform\Domains\Resource\Resource */
    protected $items;

    public function __construct()
    {
        $this->metas = ResourceFactory::make('sv_meta');
        $this->items = ResourceFactory::make('sv_meta_items');
    }

    public function save($meta, $parentId = null)
    {
        if ($meta instanceof Meta) {
            if ($meta->id()) {
                $metaEntry = $this->metas->find($meta->id());
            } else {
                if ($owner = $meta->getOwner()) {
                    $metaEntry = $meta->id() ? $this->metas->find($meta->id()) : $this->metas->create($owner);
                } else {
                    $metaEntry = $this->metas->create(['uuid' => $meta->uuid()]);
                }

                $meta->setId($metaEntry->id());
            }
            $metaEntryId = $metaEntry->id();
            $meta = $meta->compose();
        }

        foreach ($meta as $key => $value) {
            $item = $this->items
                ->newQuery()
                ->when($metaEntryId ?? null, function ($query, $value) {
                    $query->where('meta_id', $value);
                })
                ->when($parentId ?? null, function ($query, $value) {
                    $query->where('parent_item_id', $value);
                })
                ->where('key', $key)
                ->first();

            if ($item) {
                $item->update(['value' => is_array($value) ? null : $value]);
            } else {
                $item = $this->items->create([
                    'meta_id'        => $metaEntryId ?? null,
                    'parent_item_id' => $parentId ?? null,
                    'key'            => $key,
                    'value'          => is_array($value) ? null : $value,
                ]);
            }

            if (is_array($value)) {
                $this->save($value, $item->id());
            }
        }
    }

    public function load($key)
    {
        $query = $this->metas->newQuery()->with('items');
        if (is_string($uuid = $key)) {
            $entry = $query->where('uuid', $uuid)->first();
        } elseif ($key instanceof Entry) {
            $entry = $query->where('owner_type', $mc = $key->getMorphClass())->where('owner_id', $kk = $key->getKey())->first();
        }

        if (isset($entry)) {
            $meta = new Meta($this->someFunction($entry->items), $entry->uuid);
//            $meta->hydrate($entry);
            if (is_object($key)) {
                $meta->setOwnerEntry($key);
            }
            $meta->setId($entry->getKey());

            return $meta;
        }
    }

    public function resolve($entry, $owner)
    {
        $entry->load('items');
        $meta = new Meta($this->someFunction($entry->items), $entry->uuid);
//            $meta->hydrate($entry);
        $meta->setOwner($owner);
        $meta->setId($entry->getKey());

        return $meta;
    }

    public function make($entry, $owner)
    {
        return (new Meta)->setOwner($owner);
    }

    public function someFunction($items)
    {
        if (! $items) {
            return;
        }

        return $items->map(function ($item) {
            $item->load('items');

            $sub = [
                $item->key,
                $item->items->count() ? $this->someFunction($item->items) : $item->value,
            ];

            return $sub;
        })->toAssoc()->all();
    }

    public function all()
    {
        return $this->metas->newQuery()->get();
    }

    public function newQuery()
    {
        return $this->metas->newQuery();
    }
}