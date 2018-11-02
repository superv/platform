<?php

namespace SuperV\Platform\Domains\Resource\Relation;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use SuperV\Platform\Domains\Entry\EntryModelV2;

class RelationModel extends EntryModelV2
{
    protected $table = 'sv_relations';

    protected $casts = [
        'config' => 'array'
    ];

    public function getType()
    {
        return $this->type;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Model $model) {
            $model->attributes['uuid'] = Str::orderedUuid()->toString();
        });
    }
}