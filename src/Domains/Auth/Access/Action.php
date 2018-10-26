<?php

namespace SuperV\Platform\Domains\Auth\Access;

use Illuminate\Database\Eloquent\Model;

class Action extends Model
{
    protected $table = 'auth_actions';

    protected $guarded = [];

    public static function withSlug($slug)
    {
        return static::where('slug', $slug)->first();
    }

    public function getSlug()
    {
        return $this->slug;
    }
}