<?php

namespace SuperV\Platform\Domains\Resource\Generator;

class Schema
{
    public static function data()
    {
        return [
            'users' => [
                'fields' =>
                    [
                        'id'   =>
                            [
                                'field' => 'id',
                                'type'  => 'increments',
                            ],
                        'name' =>
                            [
                                'field' => 'name',
                                'type'  => 'string',
                            ],
                        'age'  =>
                            [
                                'field' => 'age',
                                'type'  => 'integer',
                            ],
                    ],
                'keys'   => [],
            ],
            'posts' => [
                'fields' =>
                    [
                        'id'      =>
                            [
                                'field' => 'id',
                                'type'  => 'increments',
                            ],
                        'title'   =>
                            [
                                'field' => 'title',
                                'type'  => 'string',
                            ],
                        'user_id' =>
                            [
                                'field'      => 'user_id',
                                'type'       => 'integer',
                                'decorators' =>
                                    [
                                        0 => 'unsigned',
                                        1 => 'index',
                                    ],
                            ],
                    ],
                'keys'   =>
                    [
                        [
                            'name'       => null,
                            'field'      => 'user_id',
                            'references' => 'id',
                            'on'         => 'users',
                            'onUpdate'   => 'RESTRICT',
                            'onDelete'   => 'RESTRICT',
                        ],
                    ],
            ],
        ];
    }
}
