<?php

namespace SuperV\Platform\Domains\Auth\Access;

use SuperV\Platform\Domains\Resource\Database\Entry\ResourceEntry;

class Role extends ResourceEntry
{
    use HasActions;

    protected $table = 'sv_auth_roles';

    /**
     * @param $slug
     * @return static
     */
    public static function withSlug($slug)
    {
        return static::where('slug', $slug)->first();
    }
}