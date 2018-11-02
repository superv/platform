<?php

namespace SuperV\Platform\Domains\Entry;

use Illuminate\Database\Eloquent\Model;

class EntryModelV2 extends Model
{
    protected $guarded = [];

    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $id
     * @return static
     */
    public static function find($id)
    {
        return static::query()->find($id);
    }
}