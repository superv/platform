<?php namespace SuperV\Platform\Domains\Database;

use SuperV\Platform\Contracts\ServiceProvider;
use SuperV\Platform\Domains\Database\Migration\MigrationServiceProvider;

class DatabaseServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->register(MigrationServiceProvider::class);
    }
}