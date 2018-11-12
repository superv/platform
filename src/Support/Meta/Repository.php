<?php

namespace SuperV\Platform\Support\Meta;

use SuperV\Platform\Domains\Resource\ResourceFactory;

class Repository
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
            if ($owner = $meta->getOwner()) {
                $metaEntry = $this->metas->create($owner);
            } else {
                $metaEntry = $this->metas->create( ['uuid' => $meta->uuid()]);
            }

            $metaEntryId = $metaEntry->id();

            $meta = $meta->compose();
        }

        foreach ($meta as $key => $value) {
            $record = $this->items->create([
                'meta_id'   => $metaEntryId ?? null,
                'parent_id' => $parentId ?? null,
                'key'       => $key,
                'value'     => is_array($value) ? null : $value,
            ]);

            if (is_array($value)) {
                $this->save($value, $record->id());
            }
        }
    }

    public function load(string $uuid)
    {
        $meta = $this->metas->newQuery()->with('items')->where('uuid', $uuid)->first();

        $items = $meta->items;

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
        return $this->metas->newQuery()->get();
    }

    public function newQuery()
    {
        return $this->metas->newQuery();
    }
}