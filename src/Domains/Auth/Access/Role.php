<?php

namespace SuperV\Platform\Domains\Auth\Access;

use Illuminate\Database\Eloquent\Model;
use SuperV\Modules\Nucleo\Domains\Entry\Entry;

class Role extends Model
{
    use HasActions;

    protected $table = 'auth_roles';

    protected $guarded = [];

    /**
     * @param $slug
     * @return static
     */
    public static function withSlug($slug)
    {
        return static::where('slug', $slug)->first();
    }


}