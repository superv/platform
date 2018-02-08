<?php

namespace SuperV\Platform\Packs\Nucleo;

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
            Prototype::where('table', $this->table)->delete();

            return;
        }

        if ($this->creating()) {
            $prototype = Prototype::create(['table' => $this->table]);
        } else {
            $prototype = Prototype::where('table', $this->table)->first();
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
            ]);
        }

        foreach ($this->commands as $command) {
            if ($command->name == 'dropColumn') {
                $prototype->fields()->whereIn('slug', $command->columns)->delete();
            }
        }
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