<?php

namespace Tests\Platform\Domains\Resource\Generator;

use Illuminate\Database\Eloquent\Model;

class ImportPostModel extends Model
{
    protected $table = 'imp_posts';

    public function user()
    {
        return $this->belongsTo(ImportUserModel::class, 'user_id');
    }
}
