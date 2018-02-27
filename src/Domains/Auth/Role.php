<?php

namespace SuperV\Platform\Domains\Auth;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $guarded = [];

    public static function withSlug($slug)
    {
        return static::where('slug', $slug)->first();
    }
}