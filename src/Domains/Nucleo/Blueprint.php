<?php

namespace SuperV\Platform\Domains\Nucleo;

use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Grammars\Grammar;

class Blueprint extends \Illuminate\Database\Schema\Blueprint
{
    public function build(Connection $connection, Grammar $grammar)
    {
        $scatters = [];
        foreach ($this->columns as $i => $column) {
            if ($column->scatter) {
                $scatters[] = array_pull($this->columns, $i);
            }
        }
        parent::build($connection, $grammar);

        if ($this->dropping()) {
            Prototype::where('slug', $this->getTable())->delete();

            return;
        }

        if ($this->creating()) {
            $prototype = Prototype::create(['slug' => $this->getTable()]);
        } else {
            $prototype = Prototype::where('slug', $this->getTable())->first();
        }

        foreach (array_merge($this->columns, $scatters) as $column) {
            if ($column->autoIncrement) {
                continue;
            }
            $rulesArray = is_array($column->rules) ? $column->rules : explode('|', $column->rules);
            $prototype->fields()->create([
                'prototype_id'  => $prototype->id,
                'slug'          => $column->name,
                'type'          => $column->type,
                'scatter'       => $column->scatter,
                'required'      => ! $column->nullable || in_array('required', $rulesArray),
                'default_value' => $column->default,
                'rules'         => $column->rules ?? null,
                'config'         => $column->config ?? null,
            ]);
        }

        foreach ($this->commands as $command) {
            if ($command->name == 'dropColumn') {
                $prototype->fields()->whereIn('slug', $command->columns)->delete();
            }
        }
    }

    protected function getSlug()
    {
        return $this->table;
    }

    /**
     * Determine if the blueprint has a drop or dropIfExists command.
     *
     * @return bool
     */
    protected function dropping()
    {
        return collect($this->commands)->contains(function ($command) {
            return $command->name == 'drop' || $command->name == 'dropIfExists';
        });
    }
}