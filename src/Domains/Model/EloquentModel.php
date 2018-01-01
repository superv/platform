<?php

namespace SuperV\Platform\Domains\Model;

use Illuminate\Database\Eloquent\Model;

class EloquentModel extends Model
{
    protected $guarded = [];

    public $timestamps = true;

    protected $titleColumn = 'name';

    public function getTable()
    {
        if (! isset($this->table)) {
            return str_replace('\\', '', snake_case(str_plural(class_basename($this))));
        }

        return parent::getTable();
    }

    /**
     * @return string
     */
    public function getTitleColumn(): string
    {
        return $this->titleColumn;
    }

    public function newEloquentBuilder($query)
    {
        return new EloquentQueryBuilder($query);
    }
}
