<?php

namespace SuperV\Platform\Domains\Auth;

use Illuminate\Database\Eloquent\Builder;
use SuperV\Platform\Domains\Table\TableBuilder;

class UserTableBuilder extends TableBuilder
{
    protected $model = User::class;

    protected $columns = [
        'id'    => [
            'attr'    => 'id',
            'heading' => 'ID',
        ],
        'email' => [
            'attr'    => 'email',
            'heading' => 'Email',
        ],
    ];

    public function onQuerying(Builder $query)
    {
        $query->whereHas('account');
    }
}