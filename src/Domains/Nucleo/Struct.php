<?php

namespace SuperV\Platform\Domains\Nucleo;

use Illuminate\Database\Eloquent\Model;

class Struct extends Model
{
    protected $guarded = [];

    protected static function boot()
    {
        static::deleted(function(Struct $prototype) {
            $prototype->members->map->delete();
        });
        parent::boot();
    }

    public function members()
    {
        return $this->hasMany(Member::class);
    }

    public function member($slug)
    {
        foreach($this->members as $member) {
            if ($member->field->slug == $slug)
                return $member;
        }
    }
}