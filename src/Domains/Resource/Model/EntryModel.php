<?php

namespace SuperV\Platform\Domains\Resource\Model;

use Illuminate\Database\Eloquent\Model;

class EntryModel extends Model
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