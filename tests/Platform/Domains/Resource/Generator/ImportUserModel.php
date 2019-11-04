<?php

namespace Tests\Platform\Domains\Resource\Generator;

use Illuminate\Database\Eloquent\Model;
use SuperV\Platform\Domains\Auth\Profile;

class ImportUserModel extends Model
{
    protected $table = 'imp_users';

    public function posts()
    {
        return $this->hasMany(ImportPostModel::class, 'user_id');
    }

    public function profile()
    {
        return $this->belongsTo(Profile::class, 'user_id');
    }

    public function anotherMethod()
    {
        $do = 'something';
        $irrelevant = true;

        return $do.$irrelevant;
    }
}