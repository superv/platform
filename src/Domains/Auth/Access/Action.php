<?php

namespace SuperV\Platform\Domains\Auth\Access;

use SuperV\Platform\Domains\Resource\Model\ResourceEntry;

class Action extends ResourceEntry
{
    protected $table = 'sv_auth_actions';

    public function getSlug()
    {
        return $this->slug;
    }

    public static function withSlug($slug)
    {
        return static::where('slug', $slug)->first();
    }
}