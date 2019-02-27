<?php

namespace SuperV\Platform\Domains\Port;

class ApiPort extends Port
{
    protected $slug = 'api';

    protected $prefix = 'sv-api';

    protected $guard = 'superv-api';

    protected $navigationSlug = 'acp';

    protected $middlewares = [
        'Barryvdh\Cors\HandleCors',
    ];
}