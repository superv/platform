<?php

namespace SuperV\Platform\Domains\Port;

use Fruitcake\Cors\HandleCors;

class ApiPort extends Port
{
    protected $slug = 'api';

    protected $baseUrl = 'sv-api';

    protected $guard = 'sv-api';

    protected $navigationSlug = 'acp';

    protected $roles = ['user'];

    protected $middlewares = [
        HandleCors::class
    ];
}