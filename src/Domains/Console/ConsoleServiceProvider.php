<?php

namespace SuperV\Platform\Domains\Console;


use Illuminate\Foundation\Providers\ComposerServiceProvider;
use Illuminate\Foundation\Providers\ConsoleSupportServiceProvider;
use SuperV\Platform\Domains\Database\Migration\MigrationServiceProvider;

class ConsoleServiceProvider extends ConsoleSupportServiceProvider
{
    /**
     * The provider class names.
     *
     * @var array
     */
    protected $providers = [
        ArtisanServiceProvider::class,
        MigrationServiceProvider::class,
        ComposerServiceProvider::class,
    ];
}