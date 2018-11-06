<?php

namespace SuperV\Platform\Domains\Resource;

use Illuminate\Support\Fluent;

/**
 * Class ResourceBlueprint
 *
 *  @method ResourceBlueprint model($model)
 *  @method ResourceBlueprint nav($nav)
 *  @method ResourceBlueprint label($label)
 *  @method ResourceBlueprint entryLabel($entryLabel)
 */
class ResourceBlueprint extends Fluent
{
    public function fill(array $attributes = [])
    {
        foreach ($attributes as $key => $value) {
            $this->attributes[$key] = $value;
        }
    }

    public function config($table, array $columns)
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

    public function guessEntryLabel(array $columns): void
    {
        $columns = collect($columns)->keyBy('name');
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
}