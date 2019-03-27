<?php

namespace SuperV\Platform\Domains\Resource;

use Illuminate\Support\Collection;
use Illuminate\Support\Fluent;

/**
 * Class ResourceConfig
 *
 * @method ResourceConfig model($model)
 * @method ResourceConfig nav($nav)
 * @method ResourceConfig keyName($name)
 * @method ResourceConfig resourceKey($key)
 * @method ResourceConfig entryLabel($entryLabel)
 */
class ResourceConfig extends Fluent
{
    protected $table;

    public function getResourceKey()
    {
        return $this->resourceKey ?? str_singular($this->table);
    }

    public function label($label)
    {
        $this->offsetSet('label', $label);
        if (! $this->resourceKey) {
            $this->resourceKey(str_slug(str_singular($label), '_'));
        }
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

        $attributes['key_name'] = $this->keyName;


        return $attributes;
    }

    public function guessEntryLabel(Collection $columns): void
    {
        if ($columns->has('name')) {
            $this->entryLabel('{name}');
        } elseif ($columns->has('title')) {
            $this->entryLabel('{title}');
        } elseif ($firstStringColumn = $columns->firstWhere('type', 'string')) {
            $this->entryLabel('{'.$firstStringColumn->name.'}');
        } else {
            $this->entryLabel(str_singular($this->label).' #{'.$this->keyName.'}');
        }
    }

    public function setTable($table)
    {
        $this->table = $table;

        return $this;
    }
}