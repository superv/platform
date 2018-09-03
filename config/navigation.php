<?php

use SuperV\Platform\Domains\Navigation\Section;

return [
    'acp' => [
        Section::make('Nucleo')
               ->icon('cog')
               ->priority('1000')
               ->ability('modules.nucleo')
               ->sections([
                   Section::make('prototypes')
                          ->icon('user')
                          ->sections([
//                              Section::make('prototypes')->url('nucleo/resources/superv/nucleo/prototypes'),
                              Section::make('columns')->url('nucleo/resources/superv/nucleo/columns'),
                          ]),
                   Section::make('resources')
                          ->icon('capsules')
                          ->sections([
                          ]),
               ]),
    ],
];