<?php

namespace SuperV\Platform\Domains\Task\Model;

use Illuminate\Database\Eloquent\Builder;

class SubtaskModel extends TaskModel
{
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(function (Builder $query) {
            $query->where('parent_id', '!=', null);
        });
    }
}