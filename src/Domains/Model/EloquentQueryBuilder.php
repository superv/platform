<?php

namespace SuperV\Platform\Domains\Model;

use Illuminate\Database\Eloquent\Builder;

class EloquentQueryBuilder extends Builder
{
    public function get($columns = ['*'])
    {
//        \Log::info($this->toSql());

        return parent::get($columns);
    }
}