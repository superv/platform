<?php

namespace SuperV\Platform\Domains\Model;

use Illuminate\Database\Eloquent\Model;

class EloquentModel extends Model
{
    protected $guarded = [];

    public $timestamps = false;

    protected $titleColumn = 'name';

    public function getTable()
    {
        if (!isset($this->table)) {
            return str_replace('\\', '', snake_case(str_plural(class_basename($this))));
        }

        return $this->table;
    }

    /**
     * @return string
     */
    public function getTitleColumn(): string
    {
        return $this->titleColumn;
    }
}
