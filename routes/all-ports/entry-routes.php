<?php

use SuperV\Platform\Domains\Resource\Http\Controllers\EntryController;

return [
    'GET@'.'sv/ent/{resource}/{id}' => [
        'as'   => 'entry.show',
        'uses' => EntryController::at('show'),
    ],

];