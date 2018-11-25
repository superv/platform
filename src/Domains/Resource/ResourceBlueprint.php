<?php

namespace SuperV\Platform\Domains\Resource;

use Illuminate\Support\Collection;
use Illuminate\Support\Fluent;

/**
 * Class ResourceBlueprint
 * @method ResourceBlueprint model($model)
 * @method ResourceBlueprint nav($nav)
 * @method ResourceBlueprint resourceKey($key)
 * @method ResourceBlueprint label($label)
 * @method ResourceBlueprint entryLabel($entryLabel)
 */
class ResourceBlueprint extends Fluent
{
    protected $table;

    public function getResourceKey()
    {
        return $this->resourceKey ?? str_singular($this->table);
    }

    public function fill(array $attributes = [])
    {
        foreach ($attributes as $key => $value) {
            $this->attributes[$key] = $value;
        }
    }

    public function config($table, Collection $columns)
    {
        if (! $this->label) {
            $this->label(ucwords(str_replace('_', ' ', $table)));
        }

        if (! $this->entryLabel) {
            $this->guessEntryLabel($columns);
        }

        $attributes = [];
        foreach ($this->attributes as $key => $value) {
            $attributes[snake_case($key)] = $value;
        }

        return $attributes;
    }

    public function guessEntryLabel(Collection $columns): void
    {
//        $columns = collect($columns)->keyBy('name');
        if ($columns->has('name')) {
            $this->entryLabel('{name}');
        } elseif ($columns->has('title')) {
            $this->entryLabel('{title}');
        } elseif ($firstStringColumn = $columns->firstWhere('type', 'string')) {
            $this->entryLabel('{'.$firstStringColumn->name.'}');
        } else {
            $this->entryLabel(str_singular($this->label).' #{id}');
        }
    }

    public function setTable($table)
    {
        $this->table = $table;

        return $this;
    }
}