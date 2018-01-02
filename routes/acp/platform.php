<?php

return [
    'platform/entries/{ticket}/delete'                      => [
        'as'   => 'superv::entries.delete',
        'uses' => 'SuperV\Platform\Http\Controllers\Entry\DeleteEntryController@index',
        'port' => 'acp',
    ],
    'platform/entries/{ticket}/edit'                        => [
        'as'   => 'superv::entries.edit',
        'uses' => 'SuperV\Platform\Http\Controllers\Entry\EditEntryController@index',
        'port' => 'acp',
    ],
    'get@platform/entries/{entry}'                          => [
        'as'   => 'superv::entries.show',
        'uses' => 'SuperV\Platform\Http\Controllers\Entry\EntriesController'.'@show',
        'port' => 'acp',
    ],
    'patch@platform/entries/{entry}'                        => [
        'as'   => 'superv::entries.show',
        'uses' => 'SuperV\Platform\Http\Controllers\Entry\EntriesController'.'@patch',
        'port' => 'acp',
    ],
    'platform/entries/{entry}/relations/{relation}/options' => [
        'as'   => 'superv::entries.relations.options',
        'uses' => 'SuperV\Platform\Http\Controllers\Entry\Relations\OptionsController@show',
        'port' => 'acp',
    ],
];