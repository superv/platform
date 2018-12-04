<?php

namespace SuperV\Platform\Domains\Auth\Access;

use SuperV\Platform\Domains\Resource\Model\ResourceEntry;

class Role extends ResourceEntry
{
    use HasActions;

    protected $table = 'auth_roles';

    /**
     * @param $slug
     * @return static
     */
    public static function withSlug($slug)
    {
        return static::where('slug', $slug)->first();
    }
}