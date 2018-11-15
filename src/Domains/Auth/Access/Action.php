<?php

namespace SuperV\Platform\Domains\Auth\Access;

use SuperV\Platform\Domains\Database\Model\Entry;

class Action extends Entry
{
    protected $table = 'auth_actions';

    public function getSlug()
    {
        return $this->slug;
    }

    public static function withSlug($slug)
    {
        return static::where('slug', $slug)->first();
    }
}