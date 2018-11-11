<?php

namespace SuperV\Platform\Support\Meta;

use SuperV\Platform\Domains\Resource\ResourceFactory;

class Repository
{
    protected $table = 'sv_meta';

    /** @var \SuperV\Platform\Domains\Resource\Resource */
    protected $resource;

    public function __construct()
    {
        $this->resource = ResourceFactory::make($this->table);
    }

    public function save(Meta $meta, $parentId = null)
    {
        $metadata = $meta->data();

        foreach ($metadata as $key => $value) {
            $record = $this->resource->create([
                'parent_id' => $parentId ?? null,
                'uuid'      => $parentId ? null : $meta->uuid(),
                'key'       => $key,
                'value'     => $value instanceof Meta ? null : $value,
            ]);

            if ($value instanceof Meta) {
                $this->save($value, $record->id());
            }
        }
    }

    public function load(string $uuid)
    {
        $items = $this->newQuery()->with('items')->where('uuid', $uuid)->get();

        $data = $this->someFunction($items);

        return new Meta($data, $uuid);
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
        return $this->resource->newQuery()->get();
    }

    public function newQuery()
    {
        return $this->resource->newQuery();
    }

    public function getResource(): \SuperV\Platform\Domains\Resource\Resource
    {
        return $this->resource;
    }
}