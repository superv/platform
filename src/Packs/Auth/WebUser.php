<?php

namespace SuperV\Platform\Packs\Auth;

use Illuminate\Database\Eloquent\Model;
use SuperV\Platform\Packs\Nucleo\Structable;

class WebUser extends Model
{
    use Structable;

    protected $guarded = [];

    protected $table = 'users_web';

    public function user()
    {
        return $this->belongsTo(app(User::class), 'user_id');
    }
}